<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace solutosoft\auditrecord;

/**
 * SerializerInterface defines serializer interface.
 */
interface SerializerInterface
{
    /**
     * Serializes given value.
     * @param mixed $value value to be serialized
     * @return string serialized value.
     */
    public function serialize($value);

}
