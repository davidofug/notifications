<?php
declare(strict_types = 1);

function  curlSession( string $url, string $method = 'GET', array $fields ) {

    $session = curl_init();

    curl_setopt_array( $session, [

        CURLOPT_URL => $url, 
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_POSTFIELDS => http_build_query($fields),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/x-www-form-urlencoded",
            "cache-control: no-cache"
        ],
    ]);

    return $session;

}

function arrayPrettyPrint( array $array ) 
{

    echo '<pre>';
        print_r( $array );
    echo '</pre>';

}

function objectPrettyPrint( $object )
{
    echo '<pre>';
        var_dump( $object );
    echo '</pre>';
}

function dateDiff( $start, $end, $format = "%R%a" ) {
  	  
	$from = date_create( $start );
	  
	$to = date_create( $end );
	
	return ( int ) date_diff( $from, $to )->format( $format );
}

function dbConnect($dataSourceName, $user, $key) {
    try {
        $connection = new PDO($dataSourceName, $user, $key);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $connection;
    }
    catch(PDOException $E)
    {
        return sprintf("Connection failed: %s", $E->getMessage() );
    }
}

function dbDisconnect($connection){
    $connection = null;
}

function getRows( $connection, string $sql, string $item= null, string $queryType='query' ) {

    $stm = $connection->{$queryType}($sql);

    if($item) :
        $stm->bindParam(":item", $item, PDO::PARAM_STR);
        $stm->execute();
    endif;

    $rows = $stm->fetchAll(PDO::FETCH_ASSOC);

    return $rows;
}

function logToEmail($emailAddress='', $poster ='SendGrid', $chunk=null) {
    if($emailAddress) :

        if($poster=='SendGrid'):
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom($emailAddress, "Gagawala Cloud Ways");
            $email->setSubject("Sending with SendGrid is Fun");
            $email->addTo("wampambadavid@gmail.com", "David Wampamba");
            $email->addContent(
                "text/plain", "and easy to do anywhere, even with PHP"
            );
            $email->addContent(
                "text/html", "<strong>and easy to do anywhere, even with PHP</strong>"
            );
            //$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $sendgrid = new \SendGrid('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');
            try {
                $response = $sendgrid->send($email);
                print $response->statusCode() . "\n";
                print_r($response->headers());
                print $response->body() . "\n";
            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
        endif;
    endif;
}

function logToFile($filePath='', $chunk) {
    if($filePath) return $filePath;

    return false;
}

function logToPhone($phone, $chunk) {
    if($phone) return $phone;

    return false;
}

function chunkToLog( string $chunk) {
    if($chunk) {

        return $chunk;

    }

    return false;
}