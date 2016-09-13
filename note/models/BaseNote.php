<?php

namespace andreev1024\note\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Base model for Note classes
 * @package andreev1024\note
 */
class BaseNote extends ActiveRecord
{
    /**
     * @var string
     */
    public $parentIdFieldName = 'id';

    /**
     * @var string If our class OrganizationNote then parentClass is Organization
     */
    public $parentClass;

    /**
     * @author Andreev <andreev1024@gmail.com>
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->parentClass) {
            if (method_exists($this, 'getParentClass')) {
                $this->parentClass = $this->getParentClass();
            } else {
                throw new InvalidConfigException('`parentClass` must be set.');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                'parent_id',
                'exist',
                'targetAttribute' => $this->parentIdFieldName,
                'targetClass' => $this->parentClass
            ],
            [['content'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            $this->parentIdFieldName => $this->parentIdFieldName,
            'parent_id' => 'parent id',
            'content' => 'content',
            'created_at' => 'created at',
            'updated_at' => 'updated at',
            'created_by' => 'created by',
            'updated_by' => 'updated by',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => false,
            ],
        ];
    }

    /**
     * Relation for parent model.
     *
     * @author Andreev <andreev1024@gmail.com>
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne($this->parentClass, ['parent_id' => $this->parentIdFieldName]);
    }
}
