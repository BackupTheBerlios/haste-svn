<?php
// vim: foldmethod=marker
/**
 *  Haste_MailSender.php
 *
 *  @author     halt feits <halt.feits@gmail.com>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

require_once 'phpmailer/class.phpmailer.php';
require_once 'phpmailer/class.pop3.php';
require_once 'Ethna/class/Ethna_MailSender.php';

// {{{ Haste_MailSender
/**
 *  メール送信クラス
 *
 *  @author     halt feits <halt.feits@gmail.com>
 *  @access     public
 *  @package    Ethna
 */
class Haste_MailSender extends Ethna_MailSender
{
    /**
     *  メールオプションを設定する
     *
     *  @access public
     *  @param  string  $option メール送信オプション
     */
    function setOption($option)
    {
        $this->option = $option;
    }

    /**
     *  メールを送信する
     *
     *  @access public
     *  @param  string  $to     メール送信先アドレス
     *  @param  string  $type   メールテンプレートタイプ
     *  @param  array   $macro  テンプレートマクロ($typeがMAILSENDER_TYPE_DIRECTの場合はメール送信内容)
     *  @param  array   $attach 添付ファイル(array('content-type' => ..., 'content' => ...))
     *  @return string  $toがnullの場合テンプレートマクロ適用後のメール内容
     */
    function send($to, $type, $macro, $attach = null)
    {
        // コンテンツ作成
        if ($type !== MAILSENDER_TYPE_DIRECT) {
            $smarty =& $this->getTemplateEngine();

            // 基本情報設定
            $smarty->assign("env_datetime", strftime('%Y年%m月%d日 %H時%M分%S秒'));
            $smarty->assign("env_useragent", $_SERVER["HTTP_USER_AGENT"]);
            $smarty->assign("env_remoteaddr", $_SERVER["REMOTE_ADDR"]);

            // デフォルトマクロ設定
            $macro = $this->_setDefaultMacro($macro);

            // ユーザ定義情報設定
            if (is_array($macro)) {
                foreach ($macro as $key => $value) {
                    $smarty->assign($key, $value);
                }
            }

            $mail = $smarty->fetch(sprintf('%s/%s', $this->mail_dir, $type));
        } else {
            $mail = $macro;
        }

        if (is_null($to)) {
            return $mail;
        }

        // 送信
        foreach (to_array($to) as $rcpt) {
            list($header, $body) = $this->_parse($mail);

            // multipart対応
            if ($attach != null) {
                $boundary = Ethna_Util::getRandom(); 

                $body = "This is a multi-part message in MIME format.\n\n" .
                    "--$boundary\n" .
                    "Content-Type: text/plain; charset=ISO-2022-JP\n\n" .
                    "$body\n" .
                    "--$boundary\n" .
                    "Content-Type: " . $attach['content-type'] . "; name=\"" . $attach['name'] . "\"\n" .
                    "Content-Transfer-Encoding: base64\n" . 
                    "Content-Disposition: attachment; filename=\"" . $attach['name'] . "\"\n\n";
                $body .= chunk_split(base64_encode($attach['content']));
                $body .= "--$boundary--";
            }

            $body = str_replace("\r\n", "\n", $body);

            // 最低限必要なヘッダを追加
            if (array_key_exists('mime-version', $header) == false) {
                $header['mime-version'] = array('Mime-Version', '1.0');
            }
            if (array_key_exists('subject', $header) == false) {
                $header['subject'] = array('Subject', 'no subject in original');
            }
            if (array_key_exists('content-type', $header) == false) {
                if ($attach == null) {
                    $header['content-type'] = array('Content-Type', 'text/plain; charset=ISO-2022-JP');
                } else {
                    $header['content-type'] = array('Content-Type', "multipart/mixed; boundary=\"$boundary\"");
                }
            }

            switch($this->config->get('use_email')) {

            case 'mail':
                return $this->mail($rcpt, $header, $body, $this->option);
                break;

            case 'popbeforesmtp':
                return $this->popbeforesmtp($rcpt, $header, $body, $this->option);
                break;

            default:
                break;

            }

        }
    }

    function mail($rcpt, $header, $body, $option)
    {
        $header_line = "";

        foreach ($header as $key => $value) {
            if ($key == 'subject') {
                // should be added by mail()
                continue;
            }
            if ($header_line != "") {
                $header_line = "$header_line\n";
            }
            $header_line .= $value[0] . ": " . $value[1];
        }

        if (!empty($option)) {
            return mail($rcpt, $header['subject'][1], $body, $header_line, $option);
        } else {
            return mail($rcpt, $header['subject'][1], $body, $header_line);
        }
    }

    function popbeforesmtp($rcpt, $header, $body, $option = "")
    {
        $debug_level = 0;

        $pop = new POP3();
        $pop->Authorise($this->config->get('pop_address'),
            $this->config->get('pop_port'),
            $this->config->get('pop_timeout'),
            $this->config->get('pop_username'),
            $this->config->get('pop_password'),
            $debug_level
        );

        $mail = new PHPMailer();

        $mail->IsSMTP();
        $mail->SMTPDebug = $debug_level;
        $mail->IsHTML(false);

        $mail->Host     = $this->config->get('smtp_address');

        $mail->From     = $this->config->get('smtp_from');
        $mail->FromName = 'Anubis';

        $mail->Subject  =  $header['subject'][1];
        $mail->Body     =  $body;
        $mail->AddAddress($rcpt, 'Anubis User');

        if (!$mail->Send())
        {
            return $mail->ErrorInfo;
        }
    }

}
// }}}
?>
