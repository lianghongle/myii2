<?php

namespace strong\web;

use strong\helpers\StringHelper;

class Response extends \yii\web\Response{
    /**
     * 保存一个url快捷方式;
     *
     * @param $url
     * @param $attachmentName
     */
    public function saveUrl($url, $attachmentName){
        $content = "[InternetShortcut]\r\nURL={$url}\r\nIDList=\r\n[{000214A0-0000-0000-C000-000000000046}]\r\nProp3=19,2";

        $attachmentName = 'url' == pathinfo($attachmentName, PATHINFO_EXTENSION) ? $attachmentName : "{$attachmentName}.url";

        return $this->sendContentAsFile($content, $attachmentName, 'application/octet-stream');
    }

    /**
     * Sends a file to the browser.
     *
     * Note that this method only prepares the response for file sending. The file is not sent
     * until [[send()]] is called explicitly or implicitly. The latter is done after you return from a controller action.
     *
     * @param string $filePath the path of the file to be sent.
     * @param string $attachmentName the file name shown to the user. If null, it will be determined from `$filePath`.
     * @param string $mimeType the MIME type of the content. If null, it will be guessed based on `$filePath`
     * @return static the response object itself
     */
    public function sendFile($filePath, $attachmentName = null, $mimeType = null){
        $attachmentName = null === $attachmentName ? basename($filePath) : $attachmentName;
        $attachmentName = $this->autoAttachmentName($attachmentName);

        return parent::sendFile($filePath, $attachmentName, $mimeType);
    }

    /**
     * Sends the specified content as a file to the browser.
     *
     * Note that this method only prepares the response for file sending. The file is not sent
     * until [[send()]] is called explicitly or implicitly. The latter is done after you return from a controller action.
     *
     * @param string $content the content to be sent. The existing [[content]] will be discarded.
     * @param string $attachmentName the file name shown to the user.
     * @param string $mimeType the MIME type of the content.
     * @return static the response object itself
     * @throws HttpException if the requested range is not satisfiable
     */
    public function sendContentAsFile($content, $attachmentName, $mimeType = 'application/octet-stream'){
        $attachmentName = $this->autoAttachmentName($attachmentName);
        return parent::sendContentAsFile($content, $attachmentName, $mimeType);
    }

    /**
     * Sends the specified stream as a file to the browser.
     *
     * Note that this method only prepares the response for file sending. The file is not sent
     * until [[send()]] is called explicitly or implicitly. The latter is done after you return from a controller action.
     *
     * @param resource $handle the handle of the stream to be sent.
     * @param string $attachmentName the file name shown to the user.
     * @param string $mimeType the MIME type of the stream content.
     * @return static the response object itself
     * @throws HttpException if the requested range cannot be satisfied.
     */
    public function sendStreamAsFile($handle, $attachmentName, $mimeType = 'application/octet-stream'){
        $attachmentName = $this->autoAttachmentName($attachmentName);
        return parent::sendStreamAsFile($handle, $attachmentName, $mimeType);
    }

    /**
     * Sends existing file to a browser as a download using x-sendfile.
     *
     * X-Sendfile is a feature allowing a web application to redirect the request for a file to the webserver
     * that in turn processes the request, this way eliminating the need to perform tasks like reading the file
     * and sending it to the user. When dealing with a lot of files (or very big files) this can lead to a great
     * increase in performance as the web application is allowed to terminate earlier while the webserver is
     * handling the request.
     *
     * The request is sent to the server through a special non-standard HTTP-header.
     * When the web server encounters the presence of such header it will discard all output and send the file
     * specified by that header using web server internals including all optimizations like caching-headers.
     *
     * As this header directive is non-standard different directives exists for different web servers applications:
     *
     * - Apache: [X-Sendfile](http://tn123.org/mod_xsendfile)
     * - Lighttpd v1.4: [X-LIGHTTPD-send-file](http://redmine.lighttpd.net/a-projects/lighttpd/wiki/X-LIGHTTPD-send-file)
     * - Lighttpd v1.5: [X-Sendfile](http://redmine.lighttpd.net/a-projects/lighttpd/wiki/X-LIGHTTPD-send-file)
     * - Nginx: [X-Accel-Redirect](http://wiki.nginx.org/XSendfile)
     * - Cherokee: [X-Sendfile and X-Accel-Redirect](http://www.cherokee-project.com/doc/other_goodies.html#x-sendfile)
     *
     * So for this method to work the X-SENDFILE option/module should be enabled by the web server and
     * a proper xHeader should be sent.
     *
     * **Note**
     *
     * This option allows to download files that are not under web folders, and even files that are otherwise protected
     * (deny from all) like `.htaccess`.
     *
     * **Side effects**
     *
     * If this option is disabled by the web server, when this method is called a download configuration dialog
     * will open but the downloaded file will have 0 bytes.
     *
     * **Known issues**
     *
     * There is a Bug with Internet Explorer 6, 7 and 8 when X-SENDFILE is used over an SSL connection, it will show
     * an error message like this: "Internet Explorer was not able to open this Internet site. The requested site
     * is either unavailable or cannot be found.". You can work around this problem by removing the `Pragma`-header.
     *
     * **Example**
     *
     * ~~~
     * Yii::$app->response->xSendFile('/home/user/Pictures/picture1.jpg');
     * ~~~
     *
     * @param string $filePath file name with full path
     * @param string $attachmentName file name shown to the user. If null, it will be determined from `$filePath`.
     * @param string $mimeType the MIME type of the file. If null, it will be determined based on `$filePath`.
     * @param string $xHeader the name of the x-sendfile header.
     * @return static the response object itself
     */
    public function  xSendFile($filePath, $attachmentName = null, $mimeType = null, $xHeader = 'X-Sendfile'){
        $attachmentName = null === $attachmentName ? basename($filePath) : $attachmentName;
        $attachmentName = $this->autoAttachmentName($attachmentName);

        return parent::xSendFile($filePath, $attachmentName, $mimeType, $xHeader);
    }

    /**
     * 防止文件名中文乱码;
     *
     * @param $attachmentName
     * @param $fromEncoding
     * @param $toEncoding
     * @return Ambigous <\strong\helpers\string;, unknown, string, multitype:mixed >
     */
    public function autoAttachmentName($attachmentName, $fromEncoding = null, $toEncoding = null){
        $fromEncoding = null === $fromEncoding ? $this->charset : $fromEncoding;

        if(null === $toEncoding){
            $toEncoding = $this->charset;
            $configuration = ['MSIE' => 'gb2312'];
            $agent = isset ($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : false;
            if(!empty($agent)){
                foreach ($configuration as $key => $value) {
                    $toEncoding = (false !== strpos ($agent, $key)) ? $value : $toEncoding;
                    break;
                }
            }
        }

        return StringHelper::autoCharset($attachmentName, $fromEncoding, $toEncoding);
    }
}
