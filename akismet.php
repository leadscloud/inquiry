<?php
// Call to verify key function
if(!akismet_verify_key('aaa7ab1fd6e9', 'http://feedback.love4026.org/'))
	echo 'can not verify your akismet api key.';
// Authenticates your Akismet API key
function akismet_verify_key( $key, $blog ) {
    $blog = urlencode($blog);
    $request = 'key='. $key .'&blog='. $blog;
    $host = $http_host = 'rest.akismet.com';
    $path = '/1.1/verify-key';
    $port = 80;
    $akismet_ua = "WordPress/3.1.1 | Akismet/2.5.3";
    $content_length = strlen( $request );
    $http_request  = "POST $path HTTP/1.0\r\n";
    $http_request .= "Host: $host\r\n";
    $http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $http_request .= "Content-Length: {$content_length}\r\n";
    $http_request .= "User-Agent: {$akismet_ua}\r\n";
    $http_request .= "\r\n";
    $http_request .= $request;
    $response = '';
    if( false != ( $fs = @fsockopen( $http_host, $port, $errno, $errstr, 10 ) ) ) {
         
        fwrite( $fs, $http_request );
 
        while ( !feof( $fs ) )
            $response .= fgets( $fs, 1160 ); // One TCP-IP packet
        fclose( $fs );
         
        $response = explode( "\r\n\r\n", $response, 2 );
    }
     
    if ( 'valid' == $response[1] )
        return true;
    else
        return false;
}


// Call to comment check
$data = array('blog' => 'http://feedback.love4026.org',
              'user_ip' => '182.68.219.146',
              'user_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2) Gecko/20100115 Firefox/3.6',
              'referrer' => 'http://www.google.com',
              'permalink' => 'http://yourblogdomainname.com/blog/post=1',
              'comment_author' => 'admin',
              'comment_author_email' => 'sbmzhcn@gmail.com',
              'comment_author_url' => 'http://www.love4026.org',
              'comment_content' => 'thanks for your share.');
echo akismet_comment_check( 'aaa7ab1fd6e9', $data )?'tures':'falses';
// Passes back true (it's spam) or false (it's ham)
function akismet_comment_check( $key, $data ) {
    $request = 'blog='. urlencode($data['blog']) .
               '&user_ip='. urlencode($data['user_ip']) .
               '&user_agent='. urlencode($data['user_agent']) .
               '&referrer='. urlencode($data['referrer']) .
               '&permalink='. urlencode($data['permalink']) .
               '&comment_author='. urlencode($data['comment_author']) .
               '&comment_author_email='. urlencode($data['comment_author_email']) .
               '&comment_author_url='. urlencode($data['comment_author_url']) .
               '&comment_content='. urlencode($data['comment_content']);
    $host = $http_host = $key.'.rest.akismet.com';
    $path = '/1.1/comment-check';
    $port = 80;
    $akismet_ua = "WordPress/3.1.1 | Akismet/2.5.3";
    $content_length = strlen( $request );
    $http_request  = "POST $path HTTP/1.0\r\n";
    $http_request .= "Host: $host\r\n";
    $http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $http_request .= "Content-Length: {$content_length}\r\n";
    $http_request .= "User-Agent: {$akismet_ua}\r\n";
    $http_request .= "\r\n";
    $http_request .= $request;
    $response = '';
    if( false != ( $fs = @fsockopen( $http_host, $port, $errno, $errstr, 10 ) ) ) {
         
        fwrite( $fs, $http_request );
 
        while ( !feof( $fs ) )
            $response .= fgets( $fs, 1160 ); // One TCP-IP packet
        fclose( $fs );
         
        $response = explode( "\r\n\r\n", $response, 2 );
    }
     
    if ( 'true' == $response[1] )
        return true;
    else
        return false;
}