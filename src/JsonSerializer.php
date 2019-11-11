<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace solutosoft\auditrecord;

use yii\base\BaseObject;
use yii\helpers\Json;

/**
 * JsonSerializer serializes data in JSON format.
 */
class JsonSerializer extends BaseObject implements SerializerInterface
{
    /**
     * @var int the encoding options. For more details please refer to
     * <http://www.php.net/manual/en/function.json-encode.php>.
     * Default is `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`.
     */
    public $options = 320;

    /**
     * {@inheritdoc}
     */
    public function serialize($value)
    {
        return Json::encode($value, $this->options);
    }

}
