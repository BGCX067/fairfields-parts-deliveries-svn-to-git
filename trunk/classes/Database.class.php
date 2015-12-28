<?php

class Database
{
  static protected $_db;
  static private $_instance;

  /**
  *	Builds the Database singleton class to be used as a wrapper against the
  * 	database and its internals.  Specifically, this constructor builds the
  *	PDO object we'll use in this class.
  *
  *	This constructor will never be used by the outside; rather it will only
  * 	be used by the method who's task is to instantiate this singleton.
  *
  *	@param		$dbLoc		Location of the MySQL database.
  *	@param		$dbName		Name of the database being used.
  *	@param		$dbUser		Username with access to database.
  *	@param		$dbPass		Password for the database user.
  */
  private function __construct ($dbLoc, $dbName, $dbUser, $dbPass)
  {
    self::$_db = new PDO (
      sprintf ("mysql: server=%s; dbname=%s", $dbLoc, $dbName),
      $dbUser,
      $dbPass
    );
  }

  /**
  *	Instantiates the singleton.  Checks to make sure there is no instance
  *	already instantiated by looking at the static property $_instance.  If
  *	it doesn't exist, created it.  Otherwise return the handle to this
  *	resource.
  *
  *	Acts as the constructor for this class.
  *
  *	@param		$dbLoc		Location of the MySQL database.
  *	@param		$dbName		Name of the database being used.
  *	@param		$dbUser		Username with access to database.
  *	@param		$dbPass		Password for the database user.
  *	@return		Resource handle
  */
  static public function start ($dbLoc, $dbName, $dbUser, $dbPass)
  {
    if (!isset (self::$_instance))
    {
      $c = __CLASS__;
      self::$_instance = new $c ($dbLoc, $dbName, $dbUser, $dbPass);
    }

    return self::$_instance;
  }

  /**
  *	Adds a new delivery to the database.
  *
  *	@param 		$partNum	Part number being delivered.
  *	@param		$location	Address it's being delivered to.
  *	@return		ID of new row.
  *	@throw		Database failure.
  */
  public function addDelivery ($partNum, $quant, $location)
  {
    $curTime = time ();
    $partNum = (!is_numeric ($partNum) ? 0 : $partNum);
    $quant = (!is_numeric ($quant) ? 0 : $quant);
    $location = htmlentities ($location);

    $sql = self::$_db->prepare ('INSERT INTO orders (part_number, address, quantity)
                                  VALUES (:part, :addr, :quant)');
    $sql->bindParam (':part', $partNum, PDO::PARAM_STR, 30);
    $sql->bindParam (':addr', $location, PDO::PARAM_STR, 250);
    $sql->bindParam (':quantity', $quant, PDO::PARAM_INT);
    $sql->execute ();
  }

  /**
  *	Gathers all deliveries set as current.
  *
  *	@return		Array containing all deliveries.
  *	@throw		Database failure.
  */
  public function getCurrentDeliveries ()
  {
    $sql = self::$_db->prepare ('SELECT date_posted, part_number, address, quantity
                                  FROM orders
                                  WHERE fulfilled = 0
                                  ORDER BY date_posted ASC');
    $sql->execute ();

    return $sql->fetchAll ();
  }

  /**
  *	Gathers all deliveries set as completed.
  *
  *	@return		Array containing all completed deliveries.
  *	@throw		Database failure.
  */
  public function getOldDeliveries ()
  {
    $sql = self::$_db->prepare ('SELECT date_posted, part_number, address, quantity
                                  FROM orders
                                  WHERE fulfilled = 0
                                  ORDER BY date_posted DESC');
    $sql->execute ();

    return $sql->fetchAll ();
  }

  /**
  *	Sets a given delivery as completed.
  *
  *	@param 		$dId	ID of the delivery to toggle.
  *	@return		ID of new row.
  *	@throw		Database failure.
  */
  public function setDeliveryAsDelivered ($dId)
  {
    $sql = self::$_db->prepare ('UPDATE orders
                                  SET fulfilled = :done
                                  WHERE id = :rId');
    $sql->bindParam (':done', 1, PDO::PARAM_INT);
    $sql->bindParam (':rId', $dId, PDO::PARAM_INT);
  }
}