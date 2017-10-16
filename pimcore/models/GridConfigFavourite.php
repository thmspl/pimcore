<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @category   Pimcore
 * @package    Version
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\Model;

/**
 * @method \Pimcore\Model\Version\Dao getDao()
 */
class GridConfigFavourite extends AbstractModel
{
    /**
     * @var int
     */
    public $ownerId;

    /**
     * @var int
     */
    public $classId;

    /**
     * @var int
     */
    public $gridConfigId;

    /**
     * @param int $id
     *
     * @return GridConfigFavourite
     */
    public static function getByOwnerAndClassId($ownerId, $classId)
    {
        $favourite = new self();
        $favourite->getDao()->getByOwnerAndClassId($ownerId, $classId);

        return $favourite;
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        $this->getDao()->save();
    }

    /**
     * Delete this favourite
     */
    public function delete()
    {
        $this->getDao()->delete();
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param int $ownerId
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
    }

    /**
     * @return int
     */
    public function getClassId()
    {
        return $this->classId;
    }

    /**
     * @param int $classId
     */
    public function setClassId($classId)
    {
        $this->classId = $classId;
    }

    /**
     * @return int
     */
    public function getGridConfigId()
    {
        return $this->gridConfigId;
    }

    /**
     * @param int $gridConfigId
     */
    public function setGridConfigId($gridConfigId)
    {
        $this->gridConfigId = $gridConfigId;
    }
}
