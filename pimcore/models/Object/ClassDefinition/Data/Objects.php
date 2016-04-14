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
 * @package    Object|Class
 * @copyright  Copyright (c) 2009-2016 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\Model\Object\ClassDefinition\Data;

use Pimcore\Model;
use Pimcore\Model\Object;
use Pimcore\Model\Element;

class Objects extends Model\Object\ClassDefinition\Data\Relations\AbstractRelations
{

    use Model\Object\ClassDefinition\Data\Extension\Relation;

    /**
     * Static type of this element
     *
     * @var string
     */
    public $fieldtype = "objects";

    /**
     * @var integer
     */
    public $width;

    /**
     * Type for the column to query
     *
     * @var integer
     */
    public $height;

    /**
     * @var int
     */
    public $maxItems;

    /**
     * Type for the column to query
     *
     * @var string
     */
    public $queryColumnType = "text";

    /**
     * Type for the generated phpdoc
     *
     * @var string
     */
    public $phpdocType = "array";

    /**
     * @var boolean
     */
    public $relationType = true;

    /**
     * @return boolean
     */
    public function getObjectsAllowed()
    {
        return true;
    }

    /**
     * @see Object\ClassDefinition\Data::getDataForResource
     * @param array $data
     * @param null|Model\Object\AbstractObject $object
     * @param mixed $params
     * @return array
     */
    public function getDataForResource($data, $object = null, $params = array())
    {
        $return = array();

        if (is_array($data) && count($data) > 0) {
            $counter = 1;
            foreach ($data as $object) {
                if ($object instanceof Object\Concrete) {
                    $return[] = array(
                        "dest_id" => $object->getId(),
                        "type" => "object",
                        "fieldname" => $this->getName(),
                        "index" => $counter
                    );
                }
                $counter++;
            }
            return $return;
        } elseif (is_array($data) and count($data)===0) {
            //give empty array if data was not null
            return array();
        } else {
            //return null if data was null - this indicates data was not loaded
            return null;
        }
    }

    /**
     * @see Object\ClassDefinition\Data::getDataFromResource
     * @param array $data
     * @param null|Model\Object\AbstractObject $object
     * @param mixed $params
     * @return array
     */
    public function getDataFromResource($data, $object = null, $params = array())
    {
        $objects = array();
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $object) {
                $o = Object::getById($object["dest_id"]);
                if ($o instanceof Object\Concrete) {
                    $objects[] = $o;
                }
            }
        }
        //must return array - otherwise this means data is not loaded
        return $objects;
    }

    /**
     * @param $data
     * @param null $object
     * @param mixed $params
     * @throws \Exception
     */
    public function getDataForQueryResource($data, $object = null, $params = array())
    {

        //return null when data is not set
        if (!$data) {
            return null;
        }

        $ids = array();

        if (is_array($data) && count($data) > 0) {
            foreach ($data as $object) {
                if ($object instanceof Object\Concrete) {
                    $ids[] = $object->getId();
                }
            }
            return "," . implode(",", $ids) . ",";
        } elseif (is_array($data) && count($data) === 0) {
            return "";
        } else {
            throw new \Exception("invalid data passed to getDataForQueryResource - must be array and it is: " . print_r($data, true));
        }
    }


    /**
     * @see Object\ClassDefinition\Data::getDataForEditmode
     * @param array $data
     * @param null|Model\Object\AbstractObject $object
     * @param mixed $params
     * @return array
     */
    public function getDataForEditmode($data, $object = null, $params = array())
    {
        $return = array();

        if (is_array($data) && count($data) > 0) {
            foreach ($data as $object) {
                if ($object instanceof Object\Concrete) {
                    $return[] = array($object->getId(), $object->getFullPath(), $object->getClassName());
                }
            }
            if (empty($return)) {
                $return = false;
            }
            return $return;
        }
        return false;
    }

    /**
     * @see Model\Object\ClassDefinition\Data::getDataFromEditmode
     * @param array $data
     * @param null|Model\Object\AbstractObject $object
     * @param mixed $params
     * @return array
     */
    public function getDataFromEditmode($data, $object = null, $params = array())
    {

        //if not set, return null
        if ($data === null or $data === false) {
            return null;
        }

        $objects = array();
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $object) {
                $o = Object::getById($object["id"]);
                if ($o) {
                    $objects[]=$o;
                }
            }
        }
        //must return array if data shall be set
        return $objects;
    }

    /**
     * @param $data
     * @param null $object
     * @param mixed $params
     * @return array
     */
    public function getDataForGrid($data, $object = null, $params = array())
    {
        if (is_array($data)) {
            $pathes = array();
            foreach ($data as $eo) {
                if ($eo instanceof Element\ElementInterface) {
                    $pathes[] = $eo->getFullPath();
                }
            }
            return $pathes;
        }
    }

    /**
     * @see Object\ClassDefinition\Data::getVersionPreview
     * @param array $data
     * @param null|Object\AbstractObject $object
     * @param mixed $params
     * @return string
     */
    public function getVersionPreview($data, $object = null, $params = array())
    {
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $o) {
                if ($o instanceof Element\ElementInterface) {
                    $pathes[] = $o->getFullPath();
                }
            }
            return implode("<br />", $pathes);
        }
    }

    /**
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param integer $width
     * @return void
     */
    public function setWidth($width)
    {
        $this->width = $this->getAsIntegerCast($width);
        return $this;
    }

    /**
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param integer $height
     * @return void
     */
    public function setHeight($height)
    {
        $this->height = $this->getAsIntegerCast($height);
        return $this;
    }

    /**
     * Checks if data is valid for current data field
     *
     * @param mixed $data
     * @param boolean $omitMandatoryCheck
     * @throws \Exception
     */
    public function checkValidity($data, $omitMandatoryCheck = false)
    {
        if (!$omitMandatoryCheck and $this->getMandatory() and empty($data)) {
            throw new Element\ValidationException("Empty mandatory field [ ".$this->getName()." ]");
        }

        if (is_array($data)) {
            foreach ($data as $o) {
                if (empty($o)) {
                    continue;
                }

                $allowClass = $this->allowObjectRelation($o);
                if (!$allowClass or !($o instanceof Object\Concrete)) {
                    if (!$allowClass && $o instanceof Object\Concrete) {
                        $id = $o->getId();
                    } else {
                        $id = "??";
                    }
                    throw new Element\ValidationException("Invalid object relation to object [".$id."] in field " . $this->getName(), null, null);
                }
            }
        }
    }

    /**
     * converts object data to a simple string value or CSV Export
     * @abstract
     * @param Object\AbstractObject $object
     * @param array $params
     * @return string
     */
    public function getForCsvExport($object, $params = array())
    {
        $data = $this->getDataFromObjectParam($object, $params);
        if (is_array($data)) {
            $paths = array();
            foreach ($data as $eo) {
                if ($eo instanceof Element\ElementInterface) {
                    $paths[] = $eo->getFullPath();
                }
            }
            return implode(",", $paths);
        } else {
            return null;
        }
    }

    /**
     * @param $importValue
     * @param null|Model\Object\AbstractObject $object
     * @param mixed $params
     * @return array|mixed
     */
    public function getFromCsvImport($importValue, $object = null, $params = array())
    {
        $values = explode(",", $importValue);

        $value = array();
        foreach ($values as $element) {
            if ($el = Object::getByPath($element)) {
                $value[] = $el;
            }
        }
        return $value;
    }


    /**
     * This is a dummy and is mostly implemented by relation types
     *
     * @param mixed $data
     * @param array $tags
     * @return array
     */
    public function getCacheTags($data, $tags = array())
    {
        $tags = is_array($tags) ? $tags : array();

        if (is_array($data) && count($data) > 0) {
            foreach ($data as $object) {
                if ($object instanceof Element\ElementInterface && !array_key_exists($object->getCacheTag(), $tags)) {
                    $tags = $object->getCacheTags($tags);
                }
            }
        }
        return $tags;
    }

    /**
     * @param $data
     * @return array
     */
    public function resolveDependencies($data)
    {
        $dependencies = array();

        if (is_array($data) && count($data) > 0) {
            foreach ($data as $o) {
                if ($o instanceof Object\AbstractObject) {
                    $dependencies["object_" . $o->getId()] = array(
                        "id" => $o->getId(),
                        "type" => "object"
                    );
                }
            }
        }
        return $dependencies;
    }

    /**
     * @param Object\AbstractObject $object
     * @param mixed $params
     * @return array|mixed|null
     */
    public function getForWebserviceExport($object, $params = array())
    {
        $data = $this->getDataFromObjectParam($object, $params);
        if (is_array($data)) {
            $items = array();
            foreach ($data as $eo) {
                if ($eo instanceof Element\ElementInterface) {
                    $items[] = array(
                        "type" => $eo->getType(),
                        "id" => $eo->getId()
                    );
                }
            }
            return $items;
        } else {
            return null;
        }
    }

    /**
     * @param mixed $value
     * @param null $object
     * @param mixed $params
     * @param null $idMapper
     * @return array|mixed
     * @throws \Exception
     */
    public function getFromWebserviceImport($value, $object = null, $params = array(), $idMapper = null)
    {
        $relatedObjects = array();
        if (empty($value)) {
            return null;
        } elseif (is_array($value)) {
            foreach ($value as $key => $item) {
                $item = (array) $item;
                $id = $item['id'];

                if ($idMapper) {
                    $id = $idMapper->getMappedId("object", $id);
                }

                $relatedObject = null;
                if ($id) {
                    $relatedObject = Object::getById($id);
                }

                if ($relatedObject instanceof Object\AbstractObject) {
                    $relatedObjects[] = $relatedObject;
                } else {
                    if (!$idMapper || !$idMapper->ignoreMappingFailures()) {
                        throw new \Exception("cannot get values from web service import - references unknown object with id [ ".$item['id']." ]");
                    } else {
                        $idMapper->recordMappingFailure("object", $object->getId(), "object", $item['id']);
                    }
                }
            }
        } else {
            throw new \Exception("cannot get values from web service import - invalid data");
        }
        return $relatedObjects;
    }


    public function preGetData($object, $params = array())
    {
        $data = null;
        if ($object instanceof Object\Concrete) {
            $data = $object->{$this->getName()};
            if ($this->getLazyLoading() and !in_array($this->getName(), $object->getO__loadedLazyFields())) {
                //$data = $this->getDataFromResource($object->getRelationData($this->getName(),true,null));
                $data = $this->load($object, array("force" => true));

                $setter = "set" . ucfirst($this->getName());
                if (method_exists($object, $setter)) {
                    $object->$setter($data);
                }
            }
        } elseif ($object instanceof Object\Localizedfield) {
            $data = $params["data"];
        } elseif ($object instanceof Object\Fieldcollection\Data\AbstractData) {
            $data = $object->{$this->getName()};
        } elseif ($object instanceof Object\Objectbrick\Data\AbstractData) {
            $data = $object->{$this->getName()};
        }


        if (Object\AbstractObject::doHideUnpublished() and is_array($data)) {
            $publishedList = array();
            foreach ($data as $listElement) {
                if (Element\Service::isPublished($listElement)) {
                    $publishedList[] = $listElement;
                }
            }
            return $publishedList;
        }
        return is_array($data) ? $data : array();
    }

    public function preSetData($object, $data, $params = array())
    {
        if ($data === null) {
            $data = array();
        }

        if ($object instanceof Object\Concrete) {
            if ($this->getLazyLoading() and !in_array($this->getName(), $object->getO__loadedLazyFields())) {
                $object->addO__loadedLazyField($this->getName());
            }
        }

        return $data;
    }

    /**
     * @param string $fieldtype
     * @return $this|void
     */
    public function setFieldtype($fieldtype)
    {
        $this->fieldtype = $fieldtype;
        return $this;
    }

    /**
     * @return string
     */
    public function getFieldtype()
    {
        return $this->fieldtype;
    }

    /**
     * @param $maxItems
     * @return $this
     */
    public function setMaxItems($maxItems)
    {
        $this->maxItems = $this->getAsIntegerCast($maxItems);
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxItems()
    {
        return $this->maxItems;
    }

    /** True if change is allowed in edit mode.
     * @param string $object
     * @param mixed $params
     * @return bool
     */
    public function isDiffChangeAllowed($object, $params = array())
    {
        return true;
    }

    /** Generates a pretty version preview (similar to getVersionPreview) can be either html or
     * a image URL. See the ObjectMerger plugin documentation for details
     * @param $data
     * @param null $object
     * @param mixed $params
     * @return array|string
     */
    public function getDiffVersionPreview($data, $object = null, $params = array())
    {
        $value = array();
        $value["type"] = "html";
        $value["html"] = "";

        if ($data) {
            $html = $this->getVersionPreview($data, $object, $params);
            $value["html"] = $html;
        }
        return $value;
    }

    /** See parent class.
     * @param $data
     * @param null $object
     * @param mixed $params
     * @return null|Pimcore_Date
     */
    public function getDiffDataFromEditmode($data, $object = null, $params = array())
    {
        if ($data) {
            $tabledata = $data[0]["data"];

            if (!$tabledata) {
                return;
            }

            $result = array();
            foreach ($tabledata as $in) {
                $out = array();
                $out["id"] = $in["id"];
                $out["path"] = $in["fullpath"];
                $out["type"] = $in["type"];
                $result[] = $out;
            }

            return $this->getDataFromEditmode($result, $object, $params);
        }
        return;
    }

    /**
     * Rewrites id from source to target, $idMapping contains
     * array(
     *  "document" => array(
     *      SOURCE_ID => TARGET_ID,
     *      SOURCE_ID => TARGET_ID
     *  ),
     *  "object" => array(...),
     *  "asset" => array(...)
     * )
     * @param mixed $object
     * @param array $idMapping
     * @param array $params
     * @return Element\ElementInterface
     */
    public function rewriteIds($object, $idMapping, $params = array())
    {
        $data = $this->getDataFromObjectParam($object, $params);
        $data = $this->rewriteIdsService($data, $idMapping);
        return $data;
    }

    /**
     * @param Object\ClassDefinition\Data $masterDefinition
     */
    public function synchronizeWithMasterDefinition(Object\ClassDefinition\Data $masterDefinition)
    {
        $this->maxItems = $masterDefinition->maxItems;
        $this->relationType = $masterDefinition->relationType;
    }


    /**
     * Override point for Enriching the layout definition before the layout is returned to the admin interface.
     */
    public function enrichLayoutDefinition($object)
    {
    }


    /**
     * @return string
     */
    public function getPhpdocType()
    {
        return implode(' | ', $this->getPhpDocClassString(true));
    }
}
