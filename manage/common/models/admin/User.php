<?php

namespace common\models\admin;

use app\modules\admin\helpers\Helper;
use common\models\admin\searchs\CompanyRole;
use common\models\system\Company;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;
use yii\helpers\ArrayHelper;
use common\models\system\CompanyGroup;

/**
 * User model
 *
 * @property integer $id
 * @property string $job_number
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property string $roles
 * @property integer $status
 * @property integer $manager_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $company_id
 * @property string $multi_company
 * @property string $password write-only password
 *
 * @property UserProfile $profile
 */
class User extends \app\components\ActiveRecordCompany implements IdentityInterface
{
    //const STATUS_INACTIVE = 0;//禁止状态
    //const STATUS_ACTIVE = 10;//活动状态
    
    public $password;
    public $roles;

    public $company_name;//所属公司名称

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        //注意：这里没有使用场景将更新和创建区别开来，是因为唯一性验证unique自动通过getIsNewRecord区分了
        return [
            [['username', 'email', 'phone','company_id'], 'required'],
            [['username', 'email', 'phone'], 'unique', 'targetClass' => '\common\models\admin\User'],
            ['phone','match','pattern'=>'/^[1][3456789][0-9]{9}$/'],
            [['company_id', 'manager_id'], 'integer'],
            ['status', 'default', 'value' => static::STATUS_INACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            ['username', 'string', 'min' => 2, 'max' => 16],
            [['job_number','multi_company'], 'string'],
            ['password', 'string', 'min' => 6],
            [['user_group_id', 'roles'], 'safe'],//指定不校验批量赋值，也可以单独验证
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('backend', 'ID'),
            'job_number' => Yii::t('backend', 'Job Number'),
            'username' => Yii::t('backend', 'User Name'),
            'email' => Yii::t('backend', 'Email'),
            'phone' => Yii::t('backend', 'Phone'),
            'roles' => Yii::t('backend', '角色权限'),
            'password' => Yii::t('backend', 'Password'),
            'status' => Yii::t('backend', 'Status'),
            'user_group_id' => Yii::t('backend', 'User Group Id'),
            'created_at' => Yii::t('backend', 'Created At'),
            'updated_at' => Yii::t('backend', 'Updated At'),
            'multi_company' => Yii::t('common', 'Belongs company'),
            'manager_id' => Yii::t('common', 'Corresponding to the Front Desk Manager account'),
        ];
    }
    
    public function afterFind()
    {
        parent::afterFind();
        
        $auth = Yii::$app->getAuthManager();
        $this->roles = ArrayHelper::map($auth->getRolesByUser($this->id), 'name', 'name');//查询出来后对roles进行填充
    }
    
    public function beforeValidate()
    {
        parent::beforeValidate();
        //权限验证
        if(!$this->getIsAdmin() && empty($this->roles)) {
            $this->addError('roles', '请选择管理者所持有的角色权限！');
            return false;
        } else {
            $this->roles = (is_array($this->roles)) ? implode('，', $this->roles): '';
        }
        
        //首次和更新密码验证
        if($this->getIsNewRecord()) {//新添
            if(empty($this->password)) {
                $this->addError('password', '首次添加操作者密码不能为空！');
            }
        } else {
            if(empty($this->oldAttributes['password_hash']) && empty($this->password)) {
                $this->addError('password', '此账号原来是没有密码的，所以请填写密码！');
            }
        }
        
        return true;
    }
    
    public function beforeSave($insert) {
        if(!parent::beforeSave($insert)) {
            return false;
        } else {
            //custom code
            //var_dump($this->attributes);exit;
            
            return true;
        }
    }
    
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        //处理角色
        $auth = Yii::$app->getAuthManager();
        //先清后加
        $auth->revokeAll($this->id);
        
        if(!$this->getIsAdmin()) {
            $this->roles = explode('，', $this->roles);
            foreach ($this->roles as $roleName) {
                $auth->assign($auth->getRole($roleName), $this->id);
            }
        }
    }
    
    public function afterDelete()
    {
        parent::afterDelete();
        $auth = Yii::$app->getAuthManager();
        $auth->revokeAll($this->id);
    }
    
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        //基于token的登录
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['phone' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
                'password_reset_token' => $token,
                'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    
    public function getUserGroup()
    {
        //理解link的顺序：一对一，即员工是已知this模型，查询员工组表，UserGroup::find()，调用是User::find()->userGroup
        //很明显，在user中有外键user_group_id已知，查的是主键，因此：['id' => 'user_group_id']
        return $this->hasOne(UserGroup::class, ['id' => 'user_group_id']);
    }

    /**
     * 查询此名用户，是否拥有管理权限
     */
    public function getIsAdmin()
    {
        return in_array($this->id, Yii::$app->params['config_admin_user_list']);
    }

    /**
     * 查询此名用户，是否拥有某种角色
     * @param string $assignments_name 角色名称
     * @return boolean true|false
     */
    public function isAssignments($assignments_name)
    {
        $assignments = Yii::$app->authManager->getAssignments($this->id);

        if (in_array($assignments_name, array_keys($assignments))) return true;

        return false;
    }
    /**
     * 查询用户是否是集群管理用户
     */
    public static function getIsGroupCompanyAdmin(){
       $user_id=Yii::$app->user->id; //当前用户id
       $companyGroup=CompanyGroup::find()->andWhere(['manage_user_id'=>$user_id])->all();
       if (empty($companyGroup)){
           return false;
       }else{
           return true;
       }
    }

    /**
     * 查询次用户是否是BU管理员权限
     */
    public static function getIsBuAdmin()
    {
        return in_array(Yii::$app->user->id, Yii::$app->params['config_bu_administrators']);
    }
    
    public static function getAllRoles($displayNull = false)
    {
        $list = [];
        if($displayNull) {
            $list['no_role'] = '无授权角色';
        }
        
        $auth = Yii::$app->getAuthManager();
        return ArrayHelper::merge($list, ArrayHelper::map($auth->getRoles(), 'name', 'name'));
    }

    //查询本公司角色
    public static function getCompanyRoles($displayNull = false)
    {
        $list = [];
        if($displayNull) {
            $list['no_role'] = '无授权角色';
        }

        $auth = Yii::$app->getAuthManager();
        $roles = ArrayHelper::map($auth->getRoles(), 'name', 'name');
        $role_list = [];
        foreach ($roles as $role_key => $role_val){
            $res = CompanyRole::compareCompany($role_val);
            if (!$res['state']){
                continue;
            }
            $role_list[$role_key] = $res['data'];

        }
        return ArrayHelper::merge($list,$role_list);
    }

    //联表公司
    public function getCompany()
    {
        // hasOne要求返回两个参数 第一个参数是关联表的类名 第二个参数是两张表的关联关系
        // 这里code是auth表关联code, 关联到当前模型的customs_code
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * @inheritdoc
     * @return BrandQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * 获取公司的后台用户（id、名称和手机号）
     * @param int $companyId 公司ID
     * @return null
     */
    public static function getCompanyUser($companyId=0){
        if(empty($companyId))
            return null;
        $userArr=User::find()->select(['id','username','phone'])->where(['company_id'=>$companyId])->asArray()->all();
        return $userArr;
    }
}
