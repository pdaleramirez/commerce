<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\models;

use craft\commerce\base\Model;
use craft\commerce\Plugin;
use craft\commerce\records\Email as EmailRecord;

/**
 * Email model.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class Email extends Model
{
    /**
     * @var int ID
     */
    public $id;

    /**
     * @var string Name
     */
    public $name;

    /**
     * @var string Subject
     */
    public $subject;

    /**
     * @var string Recipient Type
     */
    public $recipientType;

    /**
     * @var string To
     */
    public $to;

    /**
     * @var string Bcc
     */
    public $bcc;

    /**
     * @var string Cc
     */
    public $cc;

    /**
     * @var string Reply to
     */
    public $replyTo;

    /**
     * @var bool Is Enabled
     */
    public $enabled = true;

    /**
     * @var string Template path
     */
    public $templatePath;

    /**
     * @var string Plain Text Template path
     */
    public $plainTextTemplatePath;

    /**
     * @var int The PDF UID.
     */
    public $pdfId;

    /**
     * @var string UID
     */
    public $uid;


    /**
     * @inheritdoc
     */
    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['name'], 'required'];
        $rules[] = [['subject'], 'required'];
        $rules[] = [['recipientType'], 'in', 'range' => [EmailRecord::TYPE_CUSTOMER, EmailRecord::TYPE_CUSTOM]];
        $rules[] = [
            ['to'], 'required', 'when' => static function($model) {
                return $model->recipientType == EmailRecord::TYPE_CUSTOM;
            }
        ];
        $rules[] = [['templatePath'], 'required'];
        return $rules;
    }

    /**
     * @return Pdf|null
     */
    public function getPdf()
    {
        if (!$this->pdfId) {
            return null;
        }
        return Plugin::getInstance()->getPdfs()->getPdfById($this->pdfId);
    }

    /**
     * @deprecated in 3.2.0 Use $email->getPdf()->templatePath instead
     */
    public function getPdfTemplatePath()
    {
        if ($pdf = $this->getPdf()) {
            return $pdf->templatePath;
        }

        return "";
    }

    /**
     * Returns the field layout config for this email.
     *
     * @return array
     * @since 3.2.0
     */
    public function getConfig(): array
    {
        $config = [
            'name' => $this->name,
            'subject' => $this->subject,
            'recipientType' => $this->recipientType,
            'to' => $this->to ?: null,
            'bcc' => $this->bcc ?: null,
            'cc' => $this->cc ?: null,
            'replyTo' => $this->replyTo ?: null,
            'enabled' => (bool)$this->enabled,
            'plainTextTemplatePath' => $this->plainTextTemplatePath ?? null,
            'templatePath' => $this->templatePath ?: null,
        ];

        if ($pdf = $this->getPdf()) {
            $config['pdf'] = $pdf->uid;
        }

        return $config;
    }
}
