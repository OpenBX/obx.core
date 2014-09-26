<?php
namespace OBX\Core;
use OBX\Core\DBSimple\IEntity;
use OBX\Core\DBSimple\Entity;
use OBX\Core\DBSimple\IEntityStatic;
use OBX\Core\DBSimple\EntityStatic;
use OBX\Core\DBSimple\Result;


/**
 * Interface IDBSimple
 * @package OBX\Core
 * @deprecated moved to OBX\Core\DBSimple\IEntity
 */
interface IDBSimple extends IEntity{}
/**
 * Class DBSimple
 * @package OBX\Core
 * @deprecated moved to OBX\Core\DBSimple\Entity
 */
class DBSimple extends Entity {}

/**
 * Class DBSResult
 * @package OBX\Core
 * @deprecated moved to OBX\Core\DBSimple\Result
 */
class DBSResult extends Result {}

/**
 * Interface IDBSimple
 * @package OBX\Core
 * @deprecated moved to OBX\Core\DBSimple\IEntityStatic
 */
interface IDBSimpleStatic extends IEntityStatic {}
/**
 * Class DBSimple
 * @package OBX\Core
 * @deprecated moved to OBX\Core\DBSimple\EntityStatic
 */
class DBSimpleStatic extends EntityStatic {}
