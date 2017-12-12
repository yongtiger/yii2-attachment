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

namespace yongtiger\attachment\handlers;

use Yii;
use yongtiger\attachment\models\Attachment;

/**
 * Attachment Handler
 */
class AttachmentHandler
{
    /**
     * @var array
     */
    public static $attachmentTagAttributes = [
	    'img' => 'src',
	    'a' => 'href',
	    'video' => 'src',	///ueditor
    ];
	

    /**
     * Handle upload attachment by attachToken.
     *
     * @param array $attachment
     * @return bool whether the attributes are valid and the record is inserted successfully.
     */
    public static function handleUpload($attachment)
    {
        $model = new Attachment();
        $model->setAttributes([
            'attach_token' => Yii::$app->request->get('attachToken'),
            'status' => Attachment::PENDING,
            'related_url' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null,	///?????HTTP_REFERER is not always work!
            'url' => isset($attachment['url']) ? $attachment['url'] : null,
            'title' => isset($attachment['title']) ? $attachment['title'] : null,
            'original' => isset($attachment['original']) ? $attachment['original'] : null,
            'type' => isset($attachment['type']) ? $attachment['type'] : null,
            'size' => isset($attachment['size']) ? $attachment['size'] : null,
        ]);
        return $model->insert(false);
    }

    /**
     * Handle save (insert/update) attachments by attachToken.
     *
     * @param yii\db\ActiveRecord $owner
     * @param string $content
     */
    public static function handleSave($owner, $content)
    {
        $attachments = Attachment::findAll(['model' => get_class($owner), 'model_id' => $owner->id, 'status' => Attachment::APPROVED]);
        foreach ($attachments as $attachment) {
            if (!static::in_tag_attributes($attachment->url, $content, static::$attachmentTagAttributes)) {
                $attachment->updateAttributes(['model' => null, 'model_id' => null, 'status' => Attachment::DELETED]);
            }
        }

        $post = Yii::$app->request->post();
        if (!empty($attachToken = $post['attachToken'])) {
	        $attachments = Attachment::findAll(['attach_token' => $attachToken, 'status' => Attachment::PENDING]);
	        foreach ($attachments as $attachment) {
	            if (static::in_tag_attributes($attachment->url, $content, static::$attachmentTagAttributes)) {
	                $attachment->updateAttributes(['model' => get_class($owner), 'model_id' => $owner->id, 'status' => Attachment::APPROVED, 'attach_token' => null]);
	            } else {
	                $attachment->updateAttributes(['model' => null, 'model_id' => null, 'status' => Attachment::DELETED]);
	            }
	        }
        }
    }

    /**
     * Handle delete attachments.
     *
     * @param yii\db\ActiveRecord $owner
     * @return int the number of rows updated
     */
    public static function handleDelete($owner)
    {
        return Attachment::updateAll(['model' => null, 'model_id' => null, 'status' => Attachment::DELETED], ['model' => get_class($owner), 'model_id' => $owner->id, 'status' => [Attachment::APPROVED, Attachment::PENDING]]);
    }

	/**
	 * Whether or not exist in tag attributes.
	 *
	 * @param string $str
	 * @param string $html
	 * @param array $tagAttributes
	 * @return bool whether $str is exist in the tag attributes of $html.
	 */
	protected static function in_tag_attributes($str, $html, $tagAttributes) {
	    $dom = new \DOMDocument;
	    libxml_use_internal_errors(true);   ///ignore errors, e.g. in case of 'video' tag
	    $dom->loadHTML($html);
	    ///Whether or not you're using the collected warnings you should always clear the queue by calling libxml_clear_errors()
	    ///@see http://stackoverflow.com/questions/1148928/disable-warnings-when-loading-non-well-formed-html-by-domdocument-php
	    libxml_clear_errors();	///Whether or not you're using the collected warnings you should always clear the queue by calling libxml_clear_errors() @see http://stackoverflow.com/questions/1148928/disable-warnings-when-loading-non-well-formed-html-by-domdocument-php
	    foreach ($tagAttributes as $tag => $attribute) {
	        $elements = $dom->getElementsByTagName($tag);
	        foreach ($elements as $element) {
	            if (strpos($str, $element->getAttribute($attribute)) !== false) return true;
	        }
	    }
	    return false;
	}
}