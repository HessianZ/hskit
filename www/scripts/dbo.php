<?php
class DBO extends PDO 
{
    public static function create($host, $user, $pwd, $db, $charset = 'utf8', $persistent = false) 
    {
        $dsn = "mysql:host=$host;dbname=$db";
        $options = array();
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

        if ($persistent) {
            $options[PDO::ATTR_PERSISTENT] = true;
        }

        $dbo = new DBO($dsn, $user, $pwd, $options);

        $ret = $dbo->exec("SET NAMES '$charset'");

        return $dbo;
    }

    public function getOne( $sql )
    {
        if ( !($stmt = $this->query( $sql )) ) {
            return false;
        }
        $result = $stmt->fetchColumn( 0 );
        $stmt->closeCursor();

        return $result;
    }

    public function loadObject( $sql )
    {
        if ( !($stmt = $this->query( $sql )) ) {
            return false;
        }
        $result = $stmt->fetch( PDO::FETCH_OBJ );
        $stmt->closeCursor();

        return $result;
    }

    public function loadAssoc( $sql )
    {
        if ( !($stmt = $this->query( $sql )) ) {
            return false;
        }
        $result = $stmt->fetch( PDO::FETCH_ASSOC );
        $stmt->closeCursor();

        return $result;
    }

    public function loadObjectList( $sql, $key_column = "null" )
    {
        if ( !($stmt = $this->query( $sql )) ) {
            return false;
        }

        if( $key_column == "null" )
        {
            $result = $stmt->fetchAll( PDO::FETCH_OBJ );
            $stmt->closeCursor();

            return $result;
        }
        else
        {
            $ret = array();
            while( ($result = $stmt->fetch( PDO::FETCH_OBJ )) )
            {
                $ret[ $result->$key_column ] = $result;
            }
            $stmt->closeCursor();
            return $ret;
        }
    }

    public function loadAssocList( $sql )
    {
        if ( !($stmt = $this->query( $sql )) ) {
            return false;
        }
        $result = $stmt->fetchAll( PDO::FETCH_ASSOC );
        $stmt->closeCursor();

        return $result;
    }

    public function loadResultList( $sql )
    {
        if ( !($stmt = $this->query( $sql )) ) {
            return false;
        }
        $result = $stmt->fetchAll( PDO::FETCH_COLUMN );
        $stmt->closeCursor();

        return $result;
    }

    public function loadKeyPair($sql) 
    {
        if ( !($stmt = $this->query( $sql )) ) {
            return false;
        }

        $ret = array();
        while( ($result = $stmt->fetch( PDO::FETCH_NUM )) )
        {
            $ret[$result[0]] = $result[1];
        }
        $stmt->closeCursor();

        return $ret;
    }
}
