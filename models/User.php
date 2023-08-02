<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tbl_user".
 *
 * @property int $id
 * @property int|null $customer_id
 * @property int $role_id
 * @property string $user_name
 * @property string $user_email
 * @property string $password
 * @property string|null $nick_name
 * @property int|null $status
 * @property string $date_created
 * @property string|null $date_updated
 * @property string|null $auth_key
 * @property string|null $access_token
 * @property string|null $authKey
 * @property string|null $accessToken
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id', 'role_id', 'status'], 'integer'],
            [['user_name', 'user_email', 'password'], 'required'],
            [['date_created', 'date_updated'], 'safe'],
            [['user_name', 'user_email', 'password', 'auth_key', 'access_token', 'authKey', 'accessToken'], 'string', 'max' => 64],
            [['nick_name'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'role_id' => 'Role ID',
            'user_name' => 'User Name',
            'user_email' => 'User Email',
            'password' => 'Password',
            'nick_name' => 'Nick Name',
            'status' => 'Status',
            'date_created' => 'Date Created',
            'date_updated' => 'Date Updated',
            'auth_key' => 'Auth Key',
            'access_token' => 'Access Token',
            'authKey' => 'Auth Key',
            'accessToken' => 'Access Token',
        ];
    }

        /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        $user = User::findOne(['id' => $id]);
        // if(!$user){
        //     $user = User::findOne(['user_email' => $username]);
        // }
        if($user){
            return new static($user);
        }

        return null;
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        foreach (self::$users as $user) {
            if ($user['accessToken'] === $token) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        $user = User::findOne(['user_name' => $username]);
        if(!$user){
            $user = User::findOne(['user_email' => $username]);
        }
        if($user){
            return new static($user);
        }
        
        /*
        foreach (self::$users as $user) {
            if (strcasecmp($user['username'], $username) === 0) {
                return new static($user);
            }
        }
        */

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {

        if (Yii::$app->getSecurity()->validatePassword($password, $this->password)) {
            return true;
        } else {
        }   return false;
        
        // return $this->password === $password;

    }

    // parent::beforeSave($insert)

    public function beforeSave($insert) {

        if ($this->isNewRecord) {
            $this->date_created = new yii\db\Expression('UTC_TIMESTAMP');
            $hash = Yii::$app->getSecurity()->generatePasswordHash($this->password);
            $this->password = $hash;

            $this->authKey = \Yii::$app->security->generateRandomString();
            $this->accessToken = \Yii::$app->security->generateRandomString();

        } else {
            //$this->updateDate = new CDbExpression('NOW()');
            $this->authKey = \Yii::$app->security->generateRandomString();
            $this->accessToken = \Yii::$app->security->generateRandomString();

        }

        return parent::beforeSave($insert);
    }

    public function getRole()
    {
        return $this->hasOne(Role::class, ['id' => 'role_id']);
    }
}
