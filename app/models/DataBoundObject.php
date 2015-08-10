<?php

require_once 'PDOFactory.php';

abstract class DataBoundObject {

    protected $ID;
    protected $objPDO;
    protected $strTableName;
    protected $arRelationMap;
    protected $blForDeletion;
    protected $blIsLoaded;
    protected $arModifiedRelations;
    protected $IsDeleted;

    abstract protected function DefineTableName();
    abstract protected function DefineRelationMap();

    public function __construct($id = NULL, $temp = false)
    {
        # If we want to retrieve an object from temporary table
        if($temp)
            $temp = 'Temp';
        else
            $temp = '';

        $this->strTableName = $this->DefineTableName() . $temp;
        $this->arRelationMap = $this->DefineRelationMap();
        $this->objPDO = PDOFactory::get();
        $this->blIsLoaded = false;
        if(isset($id)) {
            $this->ID = $id;
        }
        $this->arModifiedRelations = array();
    }

    public function Load() {

        if(isset($this->ID)) {
            $strQuery = "SELECT ";
            // For example SELECT id,first_name,last_name,
            foreach($this->arRelationMap as $key => $value) {
                $strQuery .= $key . ",";
            }
            // Remove , at the end
            $strQuery = substr($strQuery, 0, strlen($strQuery)-1);
            $strQuery .= " FROM " . $this->strTableName . " WHERE id = :eid";

            $objStatement = $this->objPDO->prepare($strQuery);
            $objStatement->bindParam(':eid', $this->ID, PDO::PARAM_INT);

            $objStatement->execute();

            $arRow = $objStatement->fetch(PDO::FETCH_ASSOC);

            foreach($arRow as $key => $value) {
                $strMember = $this->arRelationMap[$key];
                if(property_exists($this, $strMember)) {
                    if(is_numeric($value))
                        eval('$this->' . $strMember . ' = ' . $value . ';');
                    else
                        eval('$this->' . $strMember . ' = "' . $value . '";');
                }
            }
        }

        $this->blIsLoaded = true;
    }

    public function SaveTemporary()
    {
        # First delete a row if exists
        /*$strQuery = 'DELETE FROM ' . $this->strTableName . 'Temp WHERE id = ' . $this->ID;
        $objStatement = $this->objPDO->prepare($strQuery);
        $objStatement->execute();
        unset($objStatement);*/

        # Copy existing row to a temp table
        $strQuery = 'INSERT IGNORE INTO ' . $this->strTableName . 'Temp SELECT * FROM ' . $this->strTableName . ' WHERE id=' . $this->ID;
        $objStatement = $this->objPDO->prepare($strQuery);
        $objStatement->execute();
    }

    public function RemoveTemporary()
    {
        # Remove temporary row that was created
        $strQuery = 'DELETE FROM ' . $this->strTableName . 'Temp WHERE id = ' . $this->ID;
        $objStatement = $this->objPDO->query($strQuery);
        unset($objStatement);
    }

    public function RetrieveFromTemporary()
    {
        # Remove updated row in the normal table
        $strQuery = 'DELETE FROM ' . $this->strTableName . ' WHERE id = ' . $this->ID;
        $this->objPDO->query($strQuery);

        # Copy row from temporary table to the normal table
        $strQuery = 'INSERT INTO ' . $this->strTableName . ' SELECT * FROM ' . $this->strTableName . 'Temp WHERE id=' . $this->ID;
        $this->objPDO->query($strQuery);

        # Remove the temporary row
        $this->RemoveTemporary();
    }

    public function Save() {
        if (isset($this->ID)) {
            $strQuery = 'UPDATE ' . $this->strTableName . ' SET ';
            foreach ($this->arRelationMap as $key => $value) {
                eval('$actualVal = &$this->' . $value . ';');
                if (array_key_exists($value, $this->arModifiedRelations)) {
                    $strQuery .= $key . ' = :' . $value . ', ';
                };
            }
            $strQuery = substr($strQuery, 0, strlen($strQuery)-2);
            $strQuery .= ' WHERE id = :eid';

            unset($objStatement);
            $objStatement = $this->objPDO->prepare($strQuery);
            $objStatement->bindValue(':eid', $this->ID, PDO::PARAM_INT);
            foreach ($this->arRelationMap as $key => $value) {
                eval('$actualVal = &$this->' . $value . ';');
                if (array_key_exists($value, $this->arModifiedRelations)) {
                    if ((is_int($actualVal)) || ($actualVal == NULL)) {
                        $objStatement->bindValue(':' . $value, $actualVal,
                            PDO::PARAM_INT);
                    } else {
                        $objStatement->bindValue(':' . $value, $actualVal,
                            PDO::PARAM_STR);
                    }
                }
            }

            $objStatement->execute();
        } else {
            $strValueList = "";
            $strQuery = 'INSERT INTO ' . $this->strTableName . ' (';
            foreach ($this->arRelationMap as $key => $value) {
                eval('$actualVal = &$this->' . $value . ';');
                if (isset($actualVal)) {
                    if (array_key_exists($value, $this->arModifiedRelations)) {
                        $strQuery .= '' . $key . ', ';
                        $strValueList .= ":" . $value . ", ";
                    };
                };
            }
            $strQuery = substr($strQuery, 0, strlen($strQuery) - 2);
            $strValueList = substr($strValueList, 0, strlen($strValueList) - 2);
            $strQuery .= ") VALUES (";
            $strQuery .= $strValueList;
            $strQuery .= ")";

            unset($objStatement);
            $objStatement = $this->objPDO->prepare($strQuery);
            foreach ($this->arRelationMap as $key => $value) {
                eval('$actualVal = &$this->' . $value . ';');
                if (isset($actualVal)) {
                    if (array_key_exists($value, $this->arModifiedRelations)) {
                        if ((is_int($actualVal)) || ($actualVal == NULL)) {
                            $objStatement->bindValue
                            (':' . $value, $actualVal, PDO::PARAM_INT);
                        } else {
                            $objStatement->bindValue
                            (':' . $value, $actualVal, PDO::PARAM_STR);
                        };
                    };
                };
            }
            $objStatement->execute();
            $this->ID = $this->objPDO->lastInsertId($this->strTableName . "_id_seq");
        }
    }

    /**
     * Removes an object from database
     */
    public function MarkForDeletion() {
        $this->blForDeletion = true;
    }

    public function Delete()
    {
        $this->IsDeleted = 1;
    }

    public function __destruct() {
        if(isset($this->ID)) {
            if($this->blForDeletion == true)
            {
                $strQuery = 'DELETE FROM ' . $this->strTableName . ' WHERE id = :eid';
                $objStatement = $this->objPDO->prepare($strQuery);
                $objStatement->bindValue(':eid', $this->ID, PDO::PARAM_INT);
                $objStatement->execute();
            }

            if($this->IsDeleted == 1)
            {
                $strQuery = 'UPDATE ' . $this->strTableName . ' SET is_deleted = 1 WHERE id = :eid';
                $objStatement = $this->objPDO->prepare($strQuery);
                $objStatement->bindValue(':eid', $this->ID, PDO::PARAM_INT);
                $objStatement->execute();
            }
        }
    }

    public function __call($strFunction, $arArguments) {

        $strMethodType = substr($strFunction, 0, 3);
        $strMethodMember = substr($strFunction, 3);

        switch($strMethodType) {
            case "set":
                return($this->SetAccessor($strMethodMember, $arArguments[0]));
                break;
            case "get":
                return($this->GetAccessor($strMethodMember));
        }

        return(false);

    }

    private function SetAccessor($strMember, $strNewValue) {
        if(property_exists($this, $strMember)) {
            if(is_numeric($strNewValue)) {
                eval('$this->' . $strMember . ' = ' . $strNewValue . ';');
            } else {
                eval('$this->' . $strMember . ' = "' . $strNewValue . '";');
            }
            $this->arModifiedRelations[$strMember] = "1";
        } else {
            return(false);
        }
    }

    private function GetAccessor($strMember) {
        // If object hasn't been loaded yet from database
        if ($this->blIsLoaded != true) {
            $this->Load();
        }
        if(property_exists($this, $strMember)) {
            eval('$strRetVal = $this->' . $strMember . ';');
            return($strRetVal);
        } else {
            return(false);
        }
    }
}