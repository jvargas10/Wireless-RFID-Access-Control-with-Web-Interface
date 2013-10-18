<?php

class mail
{

  static $mimetypes=array();

  /* @param $head required, assoc array: keys 'to','from','cc','bcc'
        $head = array(
            'to'      =>array('email@email.net'=>'Admin'), //arraysize: min 1, max any
            'from'    =>array('cron@55.66.77.88' =>'CronAgent'),//arraysize: min 1, max 1
            'cc'      =>array('email3@email.net'=>'Admin'),//arraysize:min 0, max any
            'bcc'     =>array('email4@email.net'=>'Admin'),//arraysize:min 0, max any
        );
        required: 'to','from'      optional: 'cc','bcc'
        multiple addresses allowed for 'to','cc','bcc'    not 'from'
   * @param $subject required, 
   * @param $body required, body of email HTML only... use <br%gt; for newline
   * @param $attachments optional, array of filenames
   * 
   * @return bool true|false depending on message send success
   */
  public static function send($head, $subject, $body, $attachments=array())
  {
    if (count($head['from'])!=1) return false;
    if (count($head['to'  ])==0) return false;
    

    $to='';
    foreach($head['to'] as $cc_addr=>$cc_name)
        $to.= $cc_name . "<" . $cc_addr . ">";
    $fromaddress = array_pop( array_keys  ($head['from']) );
    $fromname    = array_pop( array_values($head['from']) );
    $eol="\r\n";
    $mime_boundary=md5(time())."-2";
    $mime_boundary2= $mime_boundary."-3";

    # Common Headers
    $headers='';
    $headers .= "Message-ID: <".time()."-".$fromaddress.">".$eol;
    $headers .= "Date: ".date('r').$eol;
    $headers .= "From: ".$fromname."<".$fromaddress.">".$eol;
    if (isset($head['cc']))
        foreach($head['cc'] as $cc_address=>$cc_name)
            $headers .= "Cc: ".$cc_name."<".$cc_address.">".$eol;
    if (isset($head['bcc']))
        foreach($head['bcc'] as $cc_address=>$cc_name)
            $headers .= "Bcc: ".$cc_name."<".$cc_address.">".$eol;
    $headers .= "Reply-To: ".$fromname."<".$fromaddress.">".$eol;
    $headers .= "Return-Path: ".$fromname."<".$fromaddress.">".$eol;    // these two to set reply address
    //$headers .= "Message-ID: <".time()."-".$fromaddress.">".$eol;
    $headers .= "X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters

    # Boundry for marking the split & Multitype Headers
    $headers .= 'Mime-Version: 1.0'.$eol;
    $headers .= "Content-Type: multipart/mixed; boundary=\"".$mime_boundary."\"".$eol.$eol;
    #$headers .= "To: ".$to.$eol;
    #$headers .= "Subject: ".$subject.$eol.$eol;
    $headers .= "This is a MIME-formatted message.  If you see this text it means that your".$eol;
    $headers .= "E-mail software does not support MIME-formatted messages.".$eol.$eol;

    # Open the first part of the mail
    $msg ='';

    $msg .= "--".$mime_boundary.$eol;
    $msg .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary2\"".$eol.$eol;
    $msg .= "This is a MIME-formatted message.  IF you see this text it means that your".$eol;
    $msg .= "E-mail softare does not support MIME-formatted messages.".$eol.$eol;
    $msg .= "--".$mime_boundary2.$eol; 
    $msg .= "Content-Type: text/plain; charset=iso-8859-1; format=flowed".$eol;
    $msg .= "Content-Transfer-Encoding: 7bit".$eol;
    $msg .= "Content-Disposition: inline".$eol.$eol;
    $msg .= strip_tags(str_replace("<br>", "\n", $body ));
    $msg .= $eol.$eol;
    $msg .= "--".$mime_boundary2.$eol; 
    $msg .= "Content-Type: text/html; charset=iso-8859-1;".$eol;
    $msg .= "Content-Transfer-Encoding: quoted-printable".$eol;
    $msg .= "Content-Disposition: inline".$eol.$eol;
    $msg .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">".$eol;
    $msg .= "<html>".$eol;
    $msg .= "<body>".$eol;
    $msg .= mail::mime_html_encode($body).$eol;
    $msg .= "</body>".$eol;
    $msg .= "</html>".$eol;
    $msg .= $eol.$eol;
    $msg .= "--".$mime_boundary2."--".$eol.$eol; 

    for($i=0; $i < count($attachments); $i++)
    {
        if (is_file($attachments[$i]))
        {  
          # File for Attachment
          $file_name = basename($attachments[$i]);
         
          $handle=fopen($attachments[$i], 'rb');
          $f_contents=fread($handle, filesize($attachments[$i]));
          $f_contents=chunk_split(base64_encode($f_contents));    //Encode The Data For Transition using base64_encode();
          $f_type=filetype($attachments[$i]);
          fclose($handle);

          $mime_type = mail::get_mimetype( array_pop( explode(".", $attachments[$i] ) ) );
         
          # Attachment
          $msg .= "--".$mime_boundary.$eol;
          $msg .= "Content-Type: ".$mime_type."; name=\"".$file_name."\"".$eol;
          $msg .= "Content-Transfer-Encoding: base64".$eol;
          $msg .= "Content-Description: inline; $eol filename=\"".$file_name."\"".$eol.$eol; // !! This line needs TWO end of lines !! IMPORTANT !!
          $msg .= $f_contents.$eol.$eol;
        }
    }
    

    # Finished
    $msg .= "--".$mime_boundary."--".$eol.$eol;  // finish with two eol's for better security. see Injection.
   
    # SEND THE EMAIL
    ini_set('sendmail_from',$fromaddress);  // the INI lines are to force the From Address to be used !
    $mail_sent = mail($to, $subject, $msg, $headers);
   
    ini_restore('sendmail_from');
   
    return $mail_sent;
  }


  public static function get_mimetype($ext)
  {
    if (count(mail::$mimetypes)==0)
        mail::$mimetypes = mail::mimelist();
    $ext_lower = strtolower(str_replace(".","",$ext));
    if (isset( mail::$mimetypes[$ext_lower] ))
        return mail::$mimetypes[$ext_lower];

    return "application/octet-stream";
  }


  private static function mimelist()
  {
    $arr['323'  ]='text/h323';
    $arr['acx'  ]='application/internet-property-stream';
    $arr['ai'   ]='application/postscript';
    $arr['aif'  ]='audio/x-aiff';
    $arr['aifc' ]='audio/x-aiff';
    $arr['aiff' ]='audio/x-aiff';
    $arr['asf'  ]='video/x-ms-asf';
    $arr['asr'  ]='video/x-ms-asf';
    $arr['asx'  ]='video/x-ms-asf';
    $arr['au'   ]='audio/basic';
    $arr['avi'  ]='video/x-msvideo';
    $arr['axs'  ]='application/olescript';
    $arr['bas'  ]='text/plain';
    $arr['bcpio']='application/x-bcpio';
    $arr['bin'  ]='application/octet-stream';
    $arr['bmp'  ]='image/bmp';
    $arr['c'    ]='text/plain';
    $arr['cat'  ]='application/vnd.ms-pkiseccat';
    $arr['cdf'  ]='application/x-cdf';
    $arr['cer'  ]='application/x-x509-ca-cert';
    $arr['class']='application/octet-stream';
    $arr['clp'  ]='application/x-msclip';
    $arr['cmx'  ]='image/x-cmx';
    $arr['cod'  ]='image/cis-cod';
    $arr['cpio' ]='application/x-cpio';
    $arr['crd'  ]='application/x-mscardfile';
    $arr['crl'  ]='application/pkix-crl';
    $arr['crt'  ]='application/x-x509-ca-cert';
    $arr['csh'  ]='application/x-csh';
    $arr['css'  ]='text/css';
    $arr['dcr'  ]='application/x-director';
    $arr['der'  ]='application/x-x509-ca-cert';
    $arr['dir'  ]='application/x-director';
    $arr['dll'  ]='application/x-msdownload';
    $arr['dms'  ]='application/octet-stream';
    $arr['doc'  ]='application/msword';
    $arr['docx' ]='application/msword';
    $arr['dot'  ]='application/msword';
    $arr['dvi'  ]='application/x-dvi';
    $arr['dxr'  ]='application/x-director';
    $arr['eps'  ]='application/postscript';
    $arr['etx'  ]='text/x-setext';
    $arr['evy'  ]='application/envoy';
    $arr['exe'  ]='application/octet-stream';
    $arr['fif'  ]='application/fractals';
    $arr['flr'  ]='x-world/x-vrml';
    $arr['gif'  ]='image/gif';
    $arr['gtar' ]='application/x-gtar';
    $arr['gz'   ]='application/x-gzip';
    $arr['h'    ]='text/plain';
    $arr['hdf'  ]='application/x-hdf';
    $arr['hlp'  ]='application/winhlp';
    $arr['hqx'  ]='application/mac-binhex40';
    $arr['hta'  ]='application/hta';
    $arr['htc'  ]='text/x-component';
    $arr['htm'  ]='text/html';
    $arr['html' ]='text/html';
    $arr['htt'  ]='text/webviewhtml';
    $arr['ico'  ]='image/x-icon';
    $arr['ief'  ]='image/ief';
    $arr['iii'  ]='application/x-iphone';
    $arr['ins'  ]='application/x-internet-signup';
    $arr['isp'  ]='application/x-internet-signup';
    $arr['jfif' ]='image/pipeg';
    $arr['jpe'  ]='image/jpeg';
    $arr['jpeg' ]='image/jpeg';
    $arr['jpg'  ]='image/jpeg';
    $arr['js'   ]='application/x-javascript';
    $arr['latex']='application/x-latex';
    $arr['lha'  ]='application/octet-stream';
    $arr['lsf'  ]='video/x-la-asf';
    $arr['lsx'  ]='video/x-la-asf';
    $arr['lzh'  ]='application/octet-stream';
    $arr['m13'  ]='application/x-msmediaview';
    $arr['m14'  ]='application/x-msmediaview';
    $arr['m3u'  ]='audio/x-mpegurl';
    $arr['man'  ]='application/x-troff-man';
    $arr['mdb'  ]='application/x-msaccess';
    $arr['me'   ]='application/x-troff-me';
    $arr['mht'  ]='message/rfc822';
    $arr['mhtml']='message/rfc822';
    $arr['mid'  ]='audio/mid';
    $arr['mny'  ]='application/x-msmoney';
    $arr['mov'  ]='video/quicktime';
    $arr['movie']='video/x-sgi-movie';
    $arr['mp2'  ]='video/mpeg';
    $arr['mp3'  ]='audio/mpeg';
    $arr['mpa'  ]='video/mpeg';
    $arr['mpe'  ]='video/mpeg';
    $arr['mpeg' ]='video/mpeg';
    $arr['mpg'  ]='video/mpeg';
    $arr['mpp'  ]='application/vnd.ms-project';
    $arr['mpv2' ]='video/mpeg';
    $arr['ms'   ]='application/x-troff-ms';
    $arr['mvb'  ]='application/x-msmediaview';
    $arr['nws'  ]='message/rfc822';
    $arr['oda'  ]='application/oda';
    $arr['p10'  ]='application/pkcs10';
    $arr['p12'  ]='application/x-pkcs12';
    $arr['p7b'  ]='application/x-pkcs7-certificates';
    $arr['p7c'  ]='application/x-pkcs7-mime';
    $arr['p7m'  ]='application/x-pkcs7-mime';
    $arr['p7r'  ]='application/x-pkcs7-certreqresp';
    $arr['p7s'  ]='application/x-pkcs7-signature';
    $arr['pbm'  ]='image/x-portable-bitmap';
    $arr['pdf'  ]='application/pdf';
    $arr['pfx'  ]='application/x-pkcs12';
    $arr['pgm'  ]='image/x-portable-graymap';
    $arr['pko'  ]='application/ynd.ms-pkipko';
    $arr['pma'  ]='application/x-perfmon';
    $arr['pmc'  ]='application/x-perfmon';
    $arr['pml'  ]='application/x-perfmon';
    $arr['pmr'  ]='application/x-perfmon';
    $arr['pmw'  ]='application/x-perfmon';
    $arr['pnm'  ]='image/x-portable-anymap';
    $arr['pot'  ]='application/vnd.ms-powerpoint';
    $arr['ppm'  ]='image/x-portable-pixmap';
    $arr['pps'  ]='application/vnd.ms-powerpoint';
    $arr['ppt'  ]='application/vnd.ms-powerpoint';
    $arr['pptx' ]='application/vnd.ms-powerpoint';
    $arr['prf'  ]='application/pics-rules';
    $arr['ps'   ]='application/postscript';
    $arr['pub'  ]='application/x-mspublisher';
    $arr['qt'   ]='video/quicktime';
    $arr['ra'   ]='audio/x-pn-realaudio';
    $arr['ram'  ]='audio/x-pn-realaudio';
    $arr['ras'  ]='image/x-cmu-raster';
    $arr['rgb'  ]='image/x-rgb';
    $arr['rmi'  ]='audio/mid';
    $arr['roff' ]='application/x-troff';
    $arr['rtf'  ]='application/rtf';
    $arr['rtx'  ]='text/richtext';
    $arr['scd'  ]='application/x-msschedule';
    $arr['sct'  ]='text/scriptlet';
    $arr['sh'   ]='application/x-sh';
    $arr['shar' ]='application/x-shar';
    $arr['sit'  ]='application/x-stuffit';
    $arr['snd'  ]='audio/basic';
    $arr['spc'  ]='application/x-pkcs7-certificates';
    $arr['spl'  ]='application/futuresplash';
    $arr['src'  ]='application/x-wais-source';
    $arr['sst'  ]='application/vnd.ms-pkicertstore';
    $arr['stl'  ]='application/vnd.ms-pkistl';
    $arr['stm'  ]='text/html';
    $arr['svg'  ]='image/svg+xml';
    $arr['swf'  ]='application/x-shockwave-flash';
    $arr['t'    ]='application/x-troff';
    $arr['tar'  ]='application/x-tar';
    $arr['tcl'  ]='application/x-tcl';
    $arr['tex'  ]='application/x-tex';
    $arr['texi' ]='application/x-texinfo';
    $arr['tgz'  ]='application/x-compressed';
    $arr['tif'  ]='image/tiff';
    $arr['tiff' ]='image/tiff';
    $arr['tr'   ]='application/x-troff';
    $arr['trm'  ]='application/x-msterminal';
    $arr['tsv'  ]='text/tab-separated-values';
    $arr['txt'  ]='text/plain';
    $arr['uls'  ]='text/iuls';
    $arr['ustar']='application/x-ustar';
    $arr['vcf'  ]='text/x-vcard';
    $arr['vrml' ]='x-world/x-vrml';
    $arr['wav'  ]='audio/x-wav';
    $arr['wcm'  ]='application/vnd.ms-works';
    $arr['wdb'  ]='application/vnd.ms-works';
    $arr['wks'  ]='application/vnd.ms-works';
    $arr['wmf'  ]='application/x-msmetafile';
    $arr['wps'  ]='application/vnd.ms-works';
    $arr['wri'  ]='application/x-mswrite';
    $arr['wrl'  ]='x-world/x-vrml';
    $arr['wrz'  ]='x-world/x-vrml';
    $arr['xaf'  ]='x-world/x-vrml';
    $arr['xbm'  ]='image/x-xbitmap';
    $arr['xla'  ]='application/vnd.ms-excel';
    $arr['xlc'  ]='application/vnd.ms-excel';
    $arr['xlm'  ]='application/vnd.ms-excel';
    $arr['xls'  ]='application/vnd.ms-excel';
    $arr['xlsx' ]='application/vnd.ms-excel';
    $arr['xlt'  ]='application/vnd.ms-excel';
    $arr['xlw'  ]='application/vnd.ms-excel';
    $arr['xof'  ]='x-world/x-vrml';
    $arr['xpm'  ]='image/x-xpixmap';
    $arr['xwd'  ]='image/x-xwindowdump';
    $arr['z'    ]='application/x-compress';
    $arr['zip'  ]='application/zip';
    return $arr;
  }

  private function mime_html_encode($input , $line_max = 76)
  {
    
    $eol    = "\r\n";//MAIL_MIMEPART_CRLF
    $output = '';
    $line   = '';
    $intag  = false;
    
    
    for($i=0; $i<strlen($input); $i++)
    {
      $ip=$input{$i};
      $op='';
          
      if ($intag)
      {
          if ($ip=="=") $op="=3D";
          else $op= $ip;
      }
      else
      {
          if ($ip=="\"") $op='"';//'
          else if ($ip=="&") $op="&";
          else if ($ip=="'") $op="'";
          else $op= $ip;
      }


      if ((strlen($line)+strlen($op))>=$line_max)
      {
          $output.=$line.'='.$eol;
          //if ($intag) $output.=$line.'='.$eol;
          //else  $output.=$line.$eol;
          $line='';
      }
      $line.=$op;

      if($ip=='<')
          $intag=true;
      else if ($ip=='>')
          $intag=false;
    }
    return $output.$line.$eol;
  }
}
?>
