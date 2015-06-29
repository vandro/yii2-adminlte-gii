<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator mervick\adminlte\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

use Yii;
<?= $generator->timestampBehaviorNs($tableSchema) ?>
<?php if (!empty($generator->relationsSetters)): ?>
<?php foreach ($generator->relationsSetters as $rs): ?>
use <?= ltrim( $rs['many_class'], '\\') . ";\n" ?>
<?php endforeach; ?>
<?php endif; ?>

/**
 * This is the model class for table "<?= $generator->generateTableName($tableName) ?>".
 *
<?php foreach ($tableSchema->columns as $column): ?>
 * @property <?= "{$column->phpType} \${$column->name}\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{<?= $generator->statusConstants($tableSchema) ?>
    /**
     * Icon class, glyphicon or something else
     * @var string
     */
    static $icon = '<?= $generator->modelIcon ?>';
<?= $generator->imageUploadProperties($tableSchema, $tableName) ?>
<?php if (!empty($generator->relationsSetters)) :
    foreach ($generator->relationsSetters as $rs): ?>

    /**
     * @var array|null the setter to update <?= strtolower($rs['label']) ?> list
     */
    protected $_<?= $rs['property'] ?> = null;
<?php endforeach;
endif; ?>


    /**
     * Gets icon class
     * @return string
     */
    public static function icon()
    {
        return explode('-', self::$icon)[0] . ' ' . self::$icon;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '<?= $generator->generateTableName($tableName) ?>';
    }
<?php if ($generator->enableTimestampBehavior): ?>

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
<?php endif; ?>
<?php if ($generator->db !== 'db'): ?>

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('<?= $generator->db ?>');
    }
<?php endif; ?>

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [<?= "\n            " . implode(",\n            ", $rules) . "\n        " ?>];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>
        ];
    }
<?php foreach ($relations as $name => $relation): ?>

    /**
     * @return \yii\db\ActiveQuery
     */
    public function get<?= $name ?>()
    {
        <?= $relation[0] . "\n" ?>
    }
<?php endforeach; ?>
<?php if (!empty($generator->relationsSetters)): ?>
<?php foreach ($generator->relationsSetters as $rs): ?>

    /**
     * Validate {{<?= $rs['property'] ?>}} attribute.
     */
    public function validate<?= $rs['relation'] ?>()
    {
        if (is_array($this->_<?= $rs['property'] ?>)) {
            foreach ($this->_<?= $rs['property'] ?> as $id) {
                if (intval($id) != $id) {
                    $this->addError('<?= $rs['property'] ?>', 'Items of <?= $rs['label'] ?> must be integers.');
                    break;
                }
            }
        } elseif (!empty($this->_<?= $rs['property'] ?>)) {
            $this->addError('<?= $rs['property'] ?>', '<?= $rs['label'] ?> must be an array.');
        }
    }

    /**
     * Sets {{<?= $rs['property'] ?>}} attribute.
     * @param array $ids
     */
    public function set<?= $rs['relation'] ?>($ids)
    {
        $this->_<?= $rs['property'] ?> = [];
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $this->_<?= $rs['property'] ?>[] = $id;
            }
        }
    }
<?php endforeach; ?>
<?php endif; ?>
<?php if ($queryClassName): ?>
<?php
    $queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
    echo "\n";
?>
    /**
     * @inheritdoc
     * @return <?= $queryClassFullName ?> the active query used by this AR class.
     */
    public static function find()
    {
        return new <?= $queryClassFullName ?>(get_called_class());
    }

<?php endif; ?>
<?php
    $attributes = [];
    foreach ($tableSchema->columns as $column) {
        $attributes[] = $column->name;
    }
?>
<?php if (count($imageAttributes = array_intersect($generator->imageAttributes, $attributes)) > 0): ?>
    /**
     * Get the attribute's image url
     * @param string $attribute
     * @param mixed $size
     * @return null|string
     */
    protected function _getImageAttrUrl($attribute, $size)
    {
        if (!empty($this->$attribute)) {
            $index = 0;
            $attr_sizes = "{$attribute}_sizes";
            if (!empty($size)) {
                if ($size == 'large') {
                    $index = count($this->$attr_sizes) - 1;
                } else {
                    if (!is_array($size)) {
                        if (strpos($size, 'x') !== false) {
                            $size = explode('x', trim($size));
                        } else {
                            $size = [$size];
                        }
                    }
                    foreach ($this->$attr_sizes as $_index => $_size) {
                        $_size = explode('x', $_size);
                        if ($size[0] == $_size[0]) {
                            $index = $_index;
                            if (count($size) == 1) {
                                break;
                            } elseif ($size[1] == $_size[1]) {
                                break;
                            }
                        }
                    }
                }
            }
            $dir = '';
            if (($pos = strpos($upload_dir = $this->{"{$attribute}_upload_dir"}, '/web/')) !== false) {
                $dir = substr($upload_dir, $pos + 5);
            }
            return Yii::$app->request->baseUrl . '/' . implode('/', [$dir, $this->{$attr_sizes}[$index], "{$this->$attribute}"]);
        }
        return null;
    }

<?php foreach ($imageAttributes as $attr): ?>
    /**
     * Get <?= $attr ?> url
     * @param mixed $size
     * @return null|string
     */
    public function get<?= ucfirst($attr) ?>Url($size=null)
    {
        return $this->_getImageAttrUrl('<?= $attr ?>', $size);
    }

<?php endforeach; ?>
    /**
     * Upload images.
     * @param array $changedAttributes
     * @throws \yii\base\ErrorException
     */
    protected function uploadImages($changedAttributes = [])
    {
        if (!empty($_FILES)) {
            $imageAttributes = array_diff(['<?= implode('\', \'', $imageAttributes) ?>'], $changedAttributes);
            if (!empty($imageAttributes)) {
                $save = false;
                $modelName = array_reverse(explode('\\', self::className()))[0];
                foreach ($imageAttributes as $attribute) {
                    if (!empty($_FILES[$modelName]['tmp_name'][$attribute])) {
                        $upload_dir = Yii::getAlias($this->{"{$attribute}_upload_dir"});
                        $filename = $this->id . '-' . Yii::$app->security->generateRandomString(mt_rand(5, 12)) . '.jpg';
                        $old_filename = $this->$attribute;
                        $save = true;
                        foreach ($this->{"{$attribute}_sizes"} as $size) {
                            $image = \yii\image\drivers\Image::factory($_FILES[$modelName]['tmp_name'][$attribute], 'GD_extra');
                            $sizes = explode('x', $size);
                            $path = "$upload_dir/$size";
                            if (!is_dir($path)) {
                                @mkdir($path, 0777, true);
                            }
                            if (!empty($old_filename)) {
                                @unlink("$path/$old_filename");
                            }
                            if ($image->resize($sizes[0], $sizes[1], \yii\image\drivers\Image_GD_extra::CENTER)->save("$path/$filename", 85)) {
                                $this->$attribute = $filename;
                            } else {
                                $this->$attribute = '';
                            }
                        }
                        unset($_FILES[$modelName]['tmp_name'][$attribute]);
                    }
                }
                if ($save) {
                    $this->save(false);
                }
            }
        }
    }
<?php endif; ?>
<?php if (count($imageAttributes) > 0 || !empty($generator->relationsSetters)): ?>

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($isValid = parent::beforeSave($insert)) {
            if (!$insert) {
<?php if (!empty($generator->relationsSetters)): ?>
<?php foreach ($generator->relationsSetters as $rs): ?>
            if (!is_null($this->_<?= $rs['property'] ?>)) {
                <?= array_reverse(explode('\\', $rs['many_class']))[0] ?>::deleteAll('<?= $rs['many_fk'] ?> = :<?= $rs['many_fk'] ?>', [':<?= $rs['many_fk'] ?>' => $this-><?= $rs['many_id'] ?>]);
            }
<?php endforeach; ?>
<?php endif; ?>
<?php if (count($imageAttributes) > 0): ?>
                $this->uploadImages();
<?php endif; ?>
            }
        }
        return $isValid;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
<?php if (count($imageAttributes) > 0): ?>
        if ($insert) {
            $this->uploadImages($changedAttributes);
        }
<?php endif; ?>
<?php if (!empty($generator->relationsSetters)): ?>
<?php foreach ($generator->relationsSetters as $rs): ?>
        if (!is_null($this->_<?= $rs['property'] ?>)) {
            foreach ($this->_<?= $rs['property'] ?> as $id) {
                $model = new <?= array_reverse(explode('\\', $rs['many_class']))[0] ?>();
                $model-><?= $rs['many_fk'] ?> = $this->id;
                $model-><?= $rs['many_many_fk'] ?> = $id;
                $model->save();
            }
        }
<?php endforeach; ?>
<?php endif; ?>
    }
<?php endif; ?>
<?= $generator->timestampSetters($tableSchema) ?>
}