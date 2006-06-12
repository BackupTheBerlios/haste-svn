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
 *  �᡼���������饹
 *
 *  @author     halt feits <halt.feits@gmail.com>
 *  @access     public
 *  @package    Ethna
 */
class Haste_MailSender extends Ethna_MailSender
{
    /**
     *  �᡼�륪�ץ��������ꤹ��
     *
     *  @access public
     *  @param  string  $option �᡼���������ץ����
     */
    function setOption($option)
    {
        $this->option = $option;
    }

    /**
     *  �᡼�����������
     *
     *  @access public
     *  @param  string  $to     �᡼�������襢�ɥ쥹
     *  @param  string  $type   �᡼��ƥ�ץ졼�ȥ�����
     *  @param  array   $macro  �ƥ�ץ졼�ȥޥ���($type��MAILSENDER_TYPE_DIRECT�ξ��ϥ᡼����������)
     *  @param  array   $attach ź�եե�����(array('content-type' => ..., 'content' => ...))
     *  @return string  $to��null�ξ��ƥ�ץ졼�ȥޥ���Ŭ�Ѹ�Υ᡼������
     */
    function send($to, $type, $macro, $attach = null)
    {
        // ����ƥ�ĺ���
        if ($type !== MAILSENDER_TYPE_DIRECT) {
            $smarty =& $this->getTemplateEngine();

            // ���ܾ�������
            $smarty->assign("env_datetime", strftime('%Yǯ%m��%d�� %H��%Mʬ%S��'));
            $smarty->assign("env_useragent", $_SERVER["HTTP_USER_AGENT"]);
            $smarty->assign("env_remoteaddr", $_SERVER["REMOTE_ADDR"]);

            // �ǥե���ȥޥ�������
            $macro = $this->_setDefaultMacro($macro);

            // �桼�������������
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

        // ����
        foreach (to_array($to) as $rcpt) {
            list($header, $body) = $this->_parse($mail);

            // multipart�б�
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

            // �����ɬ�פʥإå����ɲ�
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
