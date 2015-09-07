<?php

require_once 'PDOFactory.php';

/**
 * This class handles the connection of objects to database
 */
abstract class DataBoundObject
{
    # Every object has an ID in the DB
    protected $ID;
    # DB connection
    protected $objPDO;
    # Name of the table in the DB
    protected $strTableName;
    # Relationship of variables with columns in the database
    protected $arRelationMap;
    # Indicates whether the object has been marked for deletion
    # and thus deleting it from DB on destroy
    protected $blForDeletion;
    # Indicates whether the properties of the object has been loaded from DB
    protected $blIsLoaded;
    # Indicates which properties have been modified during the execution of the program
    # - only properties that have been modified are updated in the database
    protected $arModifiedRelations;
    # Every object can be set as deleted
    protected $IsDeleted;

    # The following methods must be implemented by children of this class
    # This method will define the table name in the database
    abstract protected function DefineTableName();
    # This method will define the relationship of variables and columns in the database
    abstract protected function DefineRelationMap();

    public function __construct($id = NULL, $temp = false)
    {
        # If we want to retrieve an object from temporary table
        # This is used for example when we have a notification that displays
        # action point that has been changed, we want to display what was the change
        # and therefore we need the old object as well as the new one
        if($temp)
            $temp = 'Temp';
        else
            $temp = '';

        # Set instance variables
        $this->strTableName = $this->DefineTableName() . $temp;
        $this->arRelationMap = $this->DefineRelationMap();
        # Get DB connection
        $this->objPDO = PDOFactory::get();
        # By default properties are not loaded
        $this->blIsLoaded = false;
        # If we want to load a particular object
        if(isset($id))
            $this->ID = $id;

        # By default, there are no modified properties
        $this->arModifiedRelations = array();
    }

    /**
     * A method used for loading the data from DB and assigning them to the correct variables
     */
    public function Load() {

        # We want to load data only if we have ID provided
        if(isset($this->ID))
        {
            # The query is concatenated piece by piece as follows
            $strQuery = "SELECT ";
            # For example SELECT id,first_name,last_name,
            # - column names depend on the object relation map
            foreach($this->arRelationMap as $key => $value)
            {
                $strQuery .= $key . ",";
            }
            # Remove , at the end of the query
            $strQuery = substr($strQuery, 0, strlen($strQuery)-1);

            # Continuing with FROM statement, for example:
            # FROM ActionPoint (again depending on the actual object)
            # WHERE id = 1 (depending on provided ID)
            $strQuery .= " FROM " . $this->strTableName . " WHERE id = :eid";

            # Prepare the statement with provided ID
            $objStatement = $this->objPDO->prepare($strQuery);
            $objStatement->bindParam(':eid', $this->ID, PDO::PARAM_INT);

            # Execute the query
            $objStatement->execute();

            # Fetch object from database
            $arRow = $objStatement->fetch(PDO::FETCH_ASSOC);

            # The row contains a number of columns, let's go over each column,
            # assigning $key the name of the column and $value the value
            foreach($arRow as $key => $value)
            {
                # Get the actual variable name that this column value should be assigned to
                $strMember = $this->arRelationMap[$key];
                # If this variable exists in the current object
                # assign it the value from the database, for example:
                # $this->FirstName = $value
                if(property_exists($this, $strMember))
                    # Check if it's numeric or String
                    if(is_numeric($value))
                        # eval method evaluates a string as php code
                        eval('$this->' . $strMember . ' = ' . $value . ';');
                    else
                        eval('$this->' . $strMember . ' = "' . $value . '";');
            }
        }

        # The object properties have been loaded from database
        $this->blIsLoaded = true;
    }

    /**
     * A method used for saving the current object to the temporary table,
     * for example ActionPointTemp
     */
    public function SaveTemporary()
    {
        # Copy existing row to a temp table
        # IGNORE is used because we don't want to override it if it's there
        $strQuery = 'INSERT IGNORE INTO ' . $this->strTableName . 'Temp SELECT * FROM ' . $this->strTableName . ' WHERE id=' . $this->ID;
        $objStatement = $this->objPDO->prepare($strQuery);
        $objStatement->execute();
    }

    /**
     * A method used for removing the current object from the temporary table,
     * for example ActionPointTemp
     */
    public function RemoveTemporary()
    {
        # Remove temporary row that was created
        $strQuery = 'DELETE FROM ' . $this->strTableName . 'Temp WHERE id = ' . $this->ID;
        $objStatement = $this->objPDO->query($strQuery);
        unset($objStatement);
    }

    /**
     * A method used for retrieving data from temporary table back to the normal table
     */
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

    /**
     * A method used to save an object variables to the database
     */
    public function Save()
    {
        # If we created the object from an existing record in the database,
        # we will update it in the database
        if(isset($this->ID))
        {
            # Concatenate the query starting with UPDATE table_name SET
            $strQuery = 'UPDATE ' . $this->strTableName . ' SET ';
            # For each variable in the arRelationMap
            foreach ($this->arRelationMap as $key => $value)
            {
                # Get the value of the variable
                # $actualVal = $this->FirstName; for example
                eval('$actualVal = &$this->' . $value . ';');

                # If the variable was modified, it must be in arModifiedRelations table
                if (array_key_exists($value, $this->arModifiedRelations))
                    # If it's there, it's gonna be added to the query
                    # first_name = :FirstName, for example
                    $strQuery .= $key . ' = :' . $value . ', ';
            }
            # Remove the ', ' at the end of the query
            $strQuery = substr($strQuery, 0, strlen($strQuery)-2);
            # Lastly, add WHERE
            $strQuery .= ' WHERE id = :eid';

            unset($objStatement);

            # Prepare the query by adding the ID
            $objStatement = $this->objPDO->prepare($strQuery);
            $objStatement->bindValue(':eid', $this->ID, PDO::PARAM_INT);

            # Prepare the query by adding other values from the variables
            # - this way we make sure php takes care of any sql injections that might be
            #   saved in any of the variables
            foreach ($this->arRelationMap as $key => $value)
            {
                # $actualVal = $this->FirstName; for example
                eval('$actualVal = &$this->' . $value . ';');

                # If the variable was modified, it must be in arModifiedRelations table
                if (array_key_exists($value, $this->arModifiedRelations))
                {
                    # If the value is an integer or empty
                    if ((is_int($actualVal)) || ($actualVal == NULL))
                        # Bind it as an integer
                        $objStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_INT);
                    else
                        # Otherwise bind it as a string
                        $objStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_STR);
                }
            }
            # Execute the query
            $objStatement->execute();

        }
        # If we created a completely new object that has no record in the database,
        # we're gonna insert it to the database
        else
        {
            # This variable will hold values for columns
            $strValueList = "";
            # Prepare the query: INSERT INTO table_name (
            $strQuery = 'INSERT INTO ' . $this->strTableName . ' (';
            # For each column in the relation map
            foreach ($this->arRelationMap as $key => $value)
            {
                # $actualVal = $this->FirstName; for example
                eval('$actualVal = &$this->' . $value . ';');

                # If the $actualVal contained anything
                if (isset($actualVal))
                    # If the key exists in the modified relations table, then it should be
                    # added to the query, otherwise if it wasn't modified, it should not be
                    if (array_key_exists($value, $this->arModifiedRelations))
                    {
                        # for example: first_name, last_name, ...
                        $strQuery .= '' . $key . ', ';
                        # for example: :FirstName, :LastName, ...
                        $strValueList .= ":" . $value . ", ";
                    }
            }
            # Remove ', ' at the end of both query and valueList
            $strQuery = substr($strQuery, 0, strlen($strQuery) - 2);
            $strValueList = substr($strValueList, 0, strlen($strValueList) - 2);

            # Continue query with value list, for example:
            # VALUES (:FirstName, :LastName, ...)
            $strQuery .= ") VALUES (";
            $strQuery .= $strValueList;
            $strQuery .= ")";

            unset($objStatement);

            # Prepare the statement for executing
            # This way PHP takes care of mysql injection that could be in one of the instance variables
            $objStatement = $this->objPDO->prepare($strQuery);

            # For each variable in relation map
            foreach ($this->arRelationMap as $key => $value)
            {
                # $actualVal = $this->FirstName; for example
                eval('$actualVal = &$this->' . $value . ';');
                # If the variable contained anything
                if (isset($actualVal))
                {
                    # If the key exists in the modified relations table, then it should be
                    # prepared for query, otherwise if it wasn't modified, it should not be
                    if (array_key_exists($value, $this->arModifiedRelations))
                    {
                        # If the value is integer or empty, it will be prepared as integer
                        if ((is_int($actualVal)) || ($actualVal == NULL))
                            $objStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_INT);
                        # Otherwise it will be prepared as a string
                        else
                            $objStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_STR);
                    }
                }
            }
            # Execute the query
            $objStatement->execute();
            # Get the ID of the object that was generated by AUTO_INCREMENT in the database
            # and assign it to the existing object
            $this->ID = $this->objPDO->lastInsertId($this->strTableName . "_id_seq");
        }
    }

    /**
     * Marks object to be removed from database on destroy
     */
    public function MarkForDeletion()
    {
        $this->blForDeletion = true;
    }

    /**
     * Sets object as delete, which means it won't be physically removed from database,
     * however only set as deleted and kept for any future purposes
     */
    public function Delete()
    {
        $this->IsDeleted = 1;
    }

    /**
     * This method is called when the object is destructed
     * It is used to delete the object or set it as deleted, however physically keep it in the database
     */
    public function __destruct()
    {
        # This method is used only if there's a record in the database
        if(isset($this->ID))
        {
            # If we want to physically delete the object from DB
            if($this->blForDeletion == true)
            {
                $strQuery = 'DELETE FROM ' . $this->strTableName . ' WHERE id = :eid';
                $objStatement = $this->objPDO->prepare($strQuery);
                $objStatement->bindValue(':eid', $this->ID, PDO::PARAM_INT);
                $objStatement->execute();
            }

            # If we want to set the object as deleted
            if($this->IsDeleted == 1)
            {
                $strQuery = 'UPDATE ' . $this->strTableName . ' SET is_deleted = 1 WHERE id = :eid';
                $objStatement = $this->objPDO->prepare($strQuery);
                $objStatement->bindValue(':eid', $this->ID, PDO::PARAM_INT);
                $objStatement->execute();
            }
        }
    }

    /**
     * Magic PHP method used to access private instance variables of the object
     * @param $strFunction String for example getFirstName()
     * @param $arArguments String the value to be passed (in case it is mutator method)
     * @return bool false if such variable doesn't exist
     */
    public function __call($strFunction, $arArguments)
    {
        # Get the type: type always consist of 3 characters
        $strMethodType = substr($strFunction, 0, 3);
        # Get the name of the instance variable
        $strMethodMember = substr($strFunction, 3);

        switch($strMethodType)
        {
            # If it's set, call SetAccessor method with provided argument
            case "set":
                return($this->SetAccessor($strMethodMember, $arArguments[0]));
                break;
            # If it's get, just return what's returned by GetAccessor method
            case "get":
                return($this->GetAccessor($strMethodMember));
        }

        # In case it's neither set or get
        return false;

    }

    /**
     * A method to modify instance variable
     * @param $strMember String the name of the variable
     * @param $strNewValue mixed the value to be assigned
     * @return bool
     */
    private function SetAccessor($strMember, $strNewValue)
    {
        # If such variable exists
        if(property_exists($this, $strMember))
        {
            # If it's numeric, it doesn't need "" when assigned to the variable
            # For example: $this->FirstName = here_goes_integer_value;
            if(is_numeric($strNewValue))
                eval('$this->' . $strMember . ' = ' . $strNewValue . ';');
            # Otherwise it needs ""
            # For example: $this->FirstName = "here_goes_new_value";
            else
                eval('$this->' . $strMember . ' = "' . $strNewValue . '";');

            # Add this variable to modified relations since it was modified and when
            # we'll be saving data into database, we want to save the modification
            $this->arModifiedRelations[$strMember] = "1";
        }
        else
            return(false);
    }

    /**
     * A method to get the value of the instance variable
     * @param $strMember String the name of the variable
     * @return bool
     */
    private function GetAccessor($strMember)
    {
        # If object hasn't been loaded yet from database, we want to load it
        # otherwise the instance variables would be empty
        if ($this->blIsLoaded != true)
            $this->Load();

        # If such instance variable exists
        if(property_exists($this, $strMember))
        {
            # Get the value and return it
            eval('$strRetVal = $this->' . $strMember . ';');
            return($strRetVal);
        }
        else
            return(false);
    }
}