<?php
class DBMovies {
    public static function connection() {
        $conn = new PDO('mysql:host=localhost;dbname=movies', 'nameless', 'nameless');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    }

    public static function login($username, $password) {
        $conn = DBMovies::connection();

        $stmt = $conn->prepare('SELECT codice FROM users 
            WHERE bloccato = 0 AND username = :username AND password = :password;');
        $stmt->bindParam(':username', $username, PDO::PARAM_STR, 20);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR, 40);

        $stmt->execute();
        $res = $stmt->fetchAll();
        $conn = null;

        if (count($res)) return '1';
        return '0';
    }

    public static function show($order, $search) {
        $conn = DBMovies::connection();

        $sql = "SELECT nome, recensione FROM movies WHERE visibile = 1 AND user = (
            SELECT codice FROM users WHERE bloccato = 0 AND username = '".$_SESSION['username']."')";

        if ($search != '') $sql = $sql." AND nome LIKE '%".$search."%'";
        $sql = $sql." ORDER BY ".$order.";";

        $res = $conn->query($sql);
        $conn = null;
        return $res->fetchAll();
    }

    public static function download($username) {
        $conn = DBMovies::connection();

        $stmt = $conn->prepare('SELECT CONCAT(\'"\', nome, \'",\', recensione) AS riga
            FROM movies WHERE visibile = 1 AND user = (SELECT codice FROM users
            WHERE username = :username) ORDER BY ordine ASC;');
        $stmt->bindParam(':username', $username, PDO::PARAM_STR, 20);

        $stmt->execute();
        $rows = $stmt->fetchAll();
        $conn = null;

        $filename = ('/resources/saves/'.date('YmdHis').'.csv');
        $movies = fopen($_SERVER['DOCUMENT_ROOT'].$filename, 'a') or die('Error!');
        foreach ($rows as $row) fwrite($movies, $row[0].PHP_EOL);
        fclose($movies);

        $file = explode('/', $filename);
        DBMovies::logger('[SUCCESS] save movies in file {'.$file[4].'}');
        return $filename;
    }

    public static function remove($name, $review) {
        $name = str_replace('\'', '\'\'', $name);
        $conn = DBMovies::connection();

        $user = DBMovies::user();
        $sql = "UPDATE movies SET eliminazione = '".date('Ymd H:i:s').
            "', visibile = 0 WHERE nome = '".$name."' AND visibile = 1 AND user = ".$user.";";
        $res = $conn->exec($sql);
        $conn = null;

        DBMovies::logger('[SUCCESS] remove movie {\''.$name.'\', '.$review.'}');
    }

    private static function user() {
        $conn = DBMovies::connection();

        $sql = "SELECT codice FROM users WHERE bloccato = 0 AND username = '".$_SESSION['username']."';";
        $res = $conn->query($sql)->fetch();

        $conn = null;
        return $res[0];
    }

    private static function find($name) {
        $name = str_replace('\'\'', '\'', $name);
        $conn = DBMovies::connection();

        $user = DBMovies::user();
        $stmt = $conn->prepare('SELECT ordine FROM movies WHERE visibile = 1 AND nome = :name AND user = :user;');
        $stmt->bindParam(':name', $name, PDO::PARAM_STR, 50);
        $stmt->bindParam(':user', $user, PDO::PARAM_INT);

        $stmt->execute();
        $res = $stmt->fetchAll();
        $conn = null;
        return $res;
    }

    public static function create($name, $review, $order = 0, $logger = true, $import = false) {
        if ($order == 0) $name = str_replace('\'', '\'\'', $name);
        $conn = DBMovies::connection();

        $used = DBMovies::find($name);
        if (!count($used)) {
            $user = DBMovies::user();

            if (!$order) {
                $sql = "SELECT MAX(ordine) FROM movies WHERE user = ".$user." AND visibile = 1;";
                $order = $conn->query($sql)->fetch();
                $order = ++$order[0];
            }

            $sql = "INSERT INTO movies (nome, recensione, creazione, ordine, user) VALUES ('".
                $name."', ".$review.", '".date('Ymd H:i:s')."', ".$order.", ".$user.");";
            $conn->exec($sql);

            $conn = null;
            if ($logger) DBMovies::logger('[SUCCESS] create movie {\''.$name.'\', '.$review.'}', $import);
            return '1';
        }

        $conn = null;
        if ($logger) DBMovies::logger('[FAILED] create movie {\''.$name.'\', '.$review.'}', $import);
        return '0';
    }

    public static function change($name, $review, $changed, $reviewed) {
        $name = str_replace('\'', '\'\'', $name);
        $changed = str_replace('\'', '\'\'', $changed);
        $conn = DBMovies::connection();

        $user = DBMovies::user();
        $used = DBMovies::find($name);
        if (($name == $changed) || (count($used) == 0)) {
            $ordine = DBMovies::find($changed);

            $sql = "UPDATE movies SET modifica = '".date('Ymd H:i:s').
                "', visibile = 0 WHERE nome = '".$changed."' AND visibile = 1 AND user = ".$user.";";
            $conn->exec($sql);

            DBMovies::logger('[SUCCESS] change movie {\''.$changed.'\', '.$reviewed.'} => {\''.$name.'\', '.$review.'}');
            DBMovies::create($name, $review, $ordine[0][0], false);

            $conn = null;
            return '1';
        }

        $conn = null;
        DBMovies::logger('[FAILED] change movie {\''.$changed.'\', '.$reviewed.'} => {\''.$name.'\', '.$review.'}');
        return '0';
    }

    public static function charge($location, $filename) {
        $movies = fopen($location, 'r') or die('Error!');
        DBMovies::logger('[SUCCESS] load movies from file {'.$filename.'}');
        $used = 0;
        $invalid = 0;

        while (($line = fgets($movies)) != false) {
            $values = explode(',', $line);
            $values[0] = str_replace('"', '', $values[0]);

            if ((strlen($values[0]) <= 50) && (($values[1] >= 1) && ($values[1] <= 10))) {
                $res = DBMovies::create(trim($values[0]), trim($values[1]), 0, true, true);
                if ($res == '0') $used++;
            } else $invalid++;
        }

        fclose($movies);
        if ($used != 0) return '-1';
        else if ($invalid != 0) return '-2';
        return '1';
    }

    public static function logger($descrizione, $import = false) {
        $logger = fopen($_SERVER['DOCUMENT_ROOT'].'/resources/movies.log', 'a') or die('Error!');
        $line = date('d/m/Y H:i:s').' ['.$_SESSION['username'].'] '.$descrizione;
        if ($import) $line = ' +-- '.$line;

        fwrite($logger, $line.PHP_EOL);
        fclose($logger);
    }
}
?>
