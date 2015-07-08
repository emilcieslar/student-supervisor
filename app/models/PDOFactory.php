<?php

class PDOFactory
{

    public static function get()
    {
        # Load config
        $file = file_get_contents("app/config.json");
        $config = json_decode($file);

        # Prepare connection string
        $strDSN = "mysql:host={$config->{'connect'}->{'host'}};dbname={$config->{'connect'}->{'dbname'}};charset={$config->{'connect'}->{'charset'}}";

        $objPDO = null;

        # Create PDO object, if created before, just return it's instance
        try {
            $objPDO = PDOFactory::GetPDO($strDSN,$config->{'connect'}->{'username'},$config->{'connect'}->{'password'}, array(PDO::ATTR_PERSISTENT => true));
            # Show errors for testing
            $objPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "error: " . $e->getMessage();
        }

        return $objPDO;
    }

    public static function GetPDO($strDSN, $strUser, $strPass, $arParms)
    {
        $strKey = md5(serialize(array($strDSN, $strUser, $strPass, $arParms)));

        if(!isset($GLOBALS["PDOS"][$strKey])) {
            $GLOBALS["PDOS"][$strKey] = NULL;
        }

        if(!($GLOBALS["PDOS"][$strKey] instanceof PDO))
        {
            $GLOBALS["PDOS"][$strKey] = new PDO($strDSN, $strUser, $strPass, $arParms);
        }

        return($GLOBALS["PDOS"][$strKey]);
    }

}