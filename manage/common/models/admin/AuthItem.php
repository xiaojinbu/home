<?php

namespace common\models\admin;

use app\modules\admin\helpers\Helper;
use common\models\manage\MessengerRole;
use Yii;
use yii\rbac\Item;
use yii\helpers\Json;
use yii\base\Model;

/**
 * This is the model class for table "tbl_auth_item".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $ruleName
 * @property string $data
 *
 * @property Item $item
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class AuthItem extends Model
{
    public $name;
    public $type;
    public $description;
    public $ruleName;
    public $data;
    /**
     * @var Item
     */
    private $_item;

    /**
     * Initialize object
     * @param Item  $item
     * @param array $config
     */
    public function __construct($item = null, $config = [])
    {
        $this->_item = $item;
        if ($item !== null) {
            $this->name = $item->name;
            $this->type = $item->type;
            $this->description = $item->description;
            $this->ruleName = $item->ruleName;
            $this->data = $item->data === null ? null : Json::encode($item->data);
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ruleName'], 'checkRule'],
            [['name', 'type'], 'required'],
            //判断唯一性
            [['name'], 'localUnique'],
            [['type'], 'integer'],
            [['description', 'data', 'ruleName'], 'default'],
            [['name'], 'string', 'max' => 64]
        ];
    }

    /**
     * Check role is unique
     */
    public function localUnique()
    {
        if($this->isNewRecord || (!$this->isNewRecord && $this->_item->name != $this->name)) {
            $authManager = Yii::$app->authManager;
            $value = $this->name;
            if ($authManager->getRole($value) !== null || $authManager->getPermission($value) !== null) {
                $message = Yii::t('yii', '{attribute} "{value}" has already been taken.');
                $params = [
                    'attribute' => $this->getAttributeLabel('name'),
                    'value' => $value,
                ];
                $this->addError('name', Yii::$app->getI18n()->format($message, $params, Yii::$app->language));
            }
        }
    }

    /**
     * Check for rule
     */
    public function checkRule()
    {
        $name = $this->ruleName;
        if (!Yii::$app->getAuthManager()->getRule($name)) {
            try {
                $rule = Yii::createObject($name);
                if ($rule instanceof \yii\rbac\Rule) {
                    $rule->name = $name;
                    Yii::$app->getAuthManager()->add($rule);
                } else {
                    $this->addError('ruleName', Yii::t('backend', 'Invalid rule "{value}"', ['value' => $name]));
                }
            } catch (\Exception $exc) {
                $this->addError('ruleName', Yii::t('backend', 'Rule "{value}" does not exists', ['value' => $name]));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('backend', 'Name'),
            'type' => Yii::t('backend', 'Type'),
            'description' => Yii::t('backend', 'Description'),
            'ruleName' => Yii::t('backend', 'Rule Name'),
            'data' => Yii::t('backend', 'Data'),
        ];
    }

    /**
     * Check if is new record.
     * @return boolean
     */
    public function getIsNewRecord()
    {
        return $this->_item === null;
    }

    /**
     * Find role
     * @param string $id
     * @return null|\self
     */
    public static function find($id)
    {
        $item = Yii::$app->authManager->getRole($id);
        if ($item !== null) {
            return new self($item);
        }

        return null;
    }

    /**
     * Save role to [[\yii\rbac\authManager]]
     * @return boolean
     */
    public function save()
    {
        if ($this->validate()) {
            $manager = Yii::$app->authManager;
            if ($this->_item === null) {
                if ($this->type == Item::TYPE_ROLE) {
                    $this->_item = $manager->createRole($this->name);
                } else {
                    $this->_item = $manager->createPermission($this->name);
                }
                $isNew = true;
            } else {
                $isNew = false;
                $oldName = $this->_item->name;
            }
            $this->_item->name = $this->name;
            $this->_item->description = $this->description;
            $this->_item->ruleName = $this->ruleName;
            $this->_item->data = $this->data === null || $this->data === '' ? null : Json::decode($this->data);
            if ($isNew) {
                $manager->add($this->_item);
            } else {
                $manager->update($oldName, $this->_item);
            }
            
            return true;
        } else {
            return false;
        }
    }

    /**
     * Adds an item as a child of another item.
     * @param array $items
     * @return int
     */
    public function addChildren($items)
    {
        $manager = Yii::$app->getAuthManager();
        $success = 0;
        if ($this->_item) {
            foreach ($items as $name) {
                $child = $manager->getPermission($name);
                if ($this->type == Item::TYPE_ROLE && $child === null) {
                    $child = $manager->getRole($name);
                }
                try {
                    $manager->addChild($this->_item, $child);
                    $success++;
                } catch (\Exception $exc) {
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }
        
        return $success;
    }

    /**
     * Remove an item as a child of another item.
     * @param array $items
     * @return int
     */
    public function removeChildren($items)
    {
        $manager = Yii::$app->getAuthManager();
        $success = 0;
        if ($this->_item !== null) {
            foreach ($items as $name) {
                $child = $manager->getPermission($name);
                if ($this->type == Item::TYPE_ROLE && $child === null) {
                    $child = $manager->getRole($name);
                }
                try {
                    $manager->removeChild($this->_item, $child);
                    $success++;
                } catch (\Exception $exc) {
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }
        
        return $success;
    }

    /**
     * Get items
     * @return array
     */
    public function getItems()
    {
        $manager = Yii::$app->getAuthManager();
        $avaliable = [];
        $assigned = [];
        //角色，当前是角色类型，才能角色赋角色
        if ($this->type == Item::TYPE_ROLE) {
            foreach ($manager->getRoles() as $role) {
                $avaliable['role'][$role->name] = ($role->description?$role->description:Yii::t('common', 'Not set')).' => '.$role->name.' | '.($role->ruleName?$role->ruleName:Yii::t('common', 'No rules')).' => '.($role->data?Yii::t('common', 'have'):Yii::t('common', 'nothing'));
            }
        }
        //权限
        foreach ($manager->getPermissions() as $permission) {
            $avaliable[($permission->name[0] == '/' ? 'route' : 'permission')][$permission->name] = ($permission->description?$permission->description:Yii::t('common', 'Not set')).' => '.$permission->name.' | '.($permission->ruleName?$permission->ruleName:Yii::t('common', 'No rules')).' => '.($permission->data?Yii::t('common', 'have'):Yii::t('common', 'nothing'));
        }
        //路由
        foreach ($manager->getChildren($this->_item->name) as $item) {
            $type = $item->type == Item::TYPE_ROLE ? 'role' : ($item->name[0]== '/' ? 'route' : 'permission');
            $assigned[$type][$item->name] = ($item->description?$item->description:Yii::t('common', 'Not set')).' => '.$item->name.' | '.($item->ruleName?$item->ruleName:Yii::t('common', 'No rules')).' => '.($item->data?Yii::t('common', 'have'):Yii::t('common', 'nothing'));
            unset($avaliable[$type][$item->name]);
        }
        
        $curtype = $this->type == Item::TYPE_ROLE ? 'role' : ($this->name[0]== '/' ? 'route' : 'permission');
        unset($avaliable[$curtype][$this->name]);
        
        return[
            'avaliable' => $avaliable,
            'assigned' => $assigned//对的
        ];
    }
    /**
     * 获取权限
     * @return array
     */
    public function getPermission()
    {
        $manager = Yii::$app->getAuthManager();
        $avaliable = [];
        $assigned = [];
        $baStr=MessengerRole::getBa();
        $servicerStr=MessengerRole::getServicer();
        $supervisorStr=MessengerRole::getSupervisor();
        //权限
        foreach ($manager->getPermissions() as $permission) {
            $newName=str_replace('BA',$baStr,$permission->name);
            $newName=str_replace(Yii::t('common', 'Service providers'),$servicerStr,$newName);
            $newName=str_replace(Yii::t('common', 'supervisor'),$supervisorStr,$newName);
            $newDescription=str_replace('BA',$baStr,$permission->description);
            $newDescription=str_replace(Yii::t('common', 'Service providers'),$servicerStr,$newDescription);
            $newDescription=str_replace(Yii::t('common', 'supervisor'),$supervisorStr,$newDescription);
            $avaliable[($permission->name[0] == '/' ? 'route' : 'permission')][$permission->name] = $newName;
        }
        //路由
        foreach ($manager->getChildren($this->_item->name) as $item) {
            $type = $item->type == Item::TYPE_ROLE ? 'role' : ($item->name[0]== '/' ? 'route' : 'permission');

            $newName=str_replace('BA',$baStr,$item->name);
            $newName=str_replace(Yii::t('common', 'Service providers'),$servicerStr,$newName);
            $newName=str_replace(Yii::t('common', 'supervisor'),$supervisorStr,$newName);
            $newDescription=str_replace('BA',$baStr,$item->description);
            $newDescription=str_replace(Yii::t('common', 'Service providers'),$servicerStr,$newDescription);
            $newDescription=str_replace(Yii::t('common', 'supervisor'),$supervisorStr,$newDescription);

            $assigned[$type][$item->name] = $newName;
            unset($avaliable[$type][$item->name]);
        }
        unset($avaliable['route']);

        $curtype = $this->type == Item::TYPE_ROLE ? 'role' : ($this->name[0]== '/' ? 'route' : 'permission');
        unset($avaliable[$curtype][$this->name]);
        return[
            'avaliable' => $avaliable,
            'assigned' => $assigned//对的
        ];
    }
    /**
     * Get item
     * @return Item
     */
    public function getItem()
    {
        return $this->_item;
    }

    /**
     * Get type name
     * @param  mixed $type
     * @return string|array
     */
    public static function getTypeName($type = null)
    {
        $result = [
            Item::TYPE_PERMISSION => 'Permission',
            Item::TYPE_ROLE => 'Role'
        ];
        if ($type === null) {
            return $result;
        }

        return $result[$type];
    }
}
