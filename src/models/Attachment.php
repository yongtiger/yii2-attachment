<?php ///[Yii2 attachment]

/**
 * Yii2 attachment
 *
 * @link        http://www.brainbook.cc
 * @see         https://github.com/yongtiger/yii2-attachment
 * @author      Tiger Yong <tigeryang.brainbook@outlook.com>
 * @copyright   Copyright (c) 2017 BrainBook.CC
 * @license     http://opensource.org/licenses/MIT
 */

namespace yongtiger\attachment\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "attachment".
 *
 * @property integer $id
 * @property string $model
 * @property integer $model_id
 * @property string $url
 * @property string $title
 * @property string $original
 * @property integer $size
 * @property string $suffix
 * @property string $type
 * @property int $status
 * @property string $related_url
 * @property string $created_by
 * @property string $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property User $user
 */
class Attachment extends ActiveRecord
{
    const APPROVED = 1;
    const PENDING = 0;
    const DELETED = -1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%attachment}}';
    }

    /**
     * @inheritdoc
     * @return array mixed
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                // 'value' => new yii\db\Expression('NOW()'),
            ],
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_id', 'status', 'size'], 'integer'],
            [['model', 'attach_token', 'related_url', 'url', 'title', 'original', 'suffix', 'type'], 'string'],
            ['status', 'default', 'value' => static::PENDING],
            ['status', 'in', 'range' => [static::APPROVED, static::PENDING, static::DELETED]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'model' => 'Model',
            'model_id' => 'Model ID',
            'url' => 'Url',
            'title' => 'Title',
            'original' => 'Original',
            'size' => 'Size',
            'suffix' => 'Suffix',
            'type' => 'Type',
            'status' => 'Status',
            'related_url' => 'Related Url',
            'created_by' => 'Created by',
            'updated_by' => 'Updated by',
            'created_at' => 'Created date',
            'updated_at' => 'Updated date',
        ];
    }

    /**
     * Author relation
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(Yii::$app->getUser()->identityClass, ['id' => 'created_by']);
    }
}
