<?php
require_once '../vendor/autoload.php';

//echo 'Starting script<br/>';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//logToEmail($email='davidwampamba@gmail.com');

$remindInDays = [1,6,14,30,60];

$sqlInstructions = "SELECT * FROM payment_instructions WHERE status = 1";
$connection = dbConnect('mysql:host=localhost;dbname=gagraphi_demo', 'gagraphi_uza', 'jkEluFfan7dK');
$instructionRows = getRows($connection, $sqlInstructions, 'query');
$connection = null;

$instructions = [];

foreach( $instructionRows as $instructionRow ):
    $instructions[] = sprintf("<p><b>%s</b><br/> %s</p>",$instructionRow['method'], $instructionRow['instructions']);
endforeach;

$payStr = join($instructions,'');

$dotenv = Dotenv\Dotenv::create(__DIR__.'/config');
$dotenv->load();

objectPrettyPrint($_ENV);

foreach($remindInDays as $remainingDays) :

    $sessionInternetbs = curlSession('https://api.internet.bs/Domain/List',
        'POST',
        [
            'ApiKey' => 'C2O2F2K4V6L2O6Q4X2L4',
            'ExpiringOnly' => $remainingDays,
            'CompactList' => 'no',
            'Password' => '5683Gr@ph1cs',
            'ResponseFormat' => 'JSON'
        ]
    );


    $responseInternetbs = curl_exec( $sessionInternetbs );
    $errorInternetbs = curl_error( $sessionInternetbs );

    if ( $errorInternetbs ) :
        mail( "chat@david.ug", "Notifications cURL Error", "cURL Error #: {$err}" );

    else :

        $resultInternetbs = json_decode($responseInternetbs);

        if($resultInternetbs->status == 'SUCCESS') :

            if(is_array($resultInternetbs->domain) AND sizeof($resultInternetbs->domain)) :
                
                foreach($resultInternetbs->domain as $domain) :
                                      
                    if('Deleted' !== $domain->status) :
                        
                        if('Expired' !== $domain->status) :
                            
                            $today = date("Y/m/d");
                            
                            $expiration = $domain->expiration;
                            
                            $remainingDaysToExpire = dateDiff($today, $expiration);
                            
                            //$domain->name,' ',$domain->status,' date ', $expiration,' - days ', $remainingDaysToExpire,'<br/>';

                            if($remainingDaysToExpire >= 0 AND $remainingDaysToExpire == $remainingDays) :
                                
                                //printf("<p> {$domain->name} expires in {$remainingDays} status {$domain->status} expires on {$domain->expiration}</p>");
                                
                                $expireStatement = ['has expired','is expiring in 1 day',"will expire in {$remainingDays} days"];

                                $domain_name = str_replace('.','-',$domain->name);

                                $sessionCRM = curlSession("https://gagawala.com/minicrm/wp-json/api/v1/product/$domain_name",'GET',[]);
                                $responseCRM = curl_exec($sessionCRM);
                                
                                $errorCRM = curl_error($sessionCRM);

                                if ( $errorCRM ) :
                                    mail( "chat@david.ug", "Notifications cURL Error", "cURL Error #: {$err}" );
                                else:

                                    $details = json_decode($responseCRM);

                                    $msgPlainText = sprintf("
                                        Hi %s,\n        
                                        The %s (%s) service %s on %s.
        
                                        You\'re advised to submit a payment amount <b>UGX%s/=</b> as soon as possible to avoid service interruptions.\n
                                        Ignore this noification if you\'ve already submitted your payment
                                        Choose how you like to send payment and send.
                                        %s
                                
                                        Note: For urgent reinstatement of service, inform us by replying to this email with your payment receipt or transaction ID.\n
                                        Activation of expired services
                                        40%% extra charge after 30 days expiry.
                                        UGX250,000/= extra charge after 60 days expiry.
                                    
        
                                        Thank you,\nGagawala\nManagement
                                        ", 
                                        $details->customer->first_name,
                                        $details->category,
                                        $domain->name,
                                        $expireStatement[($remainingDaysToExpire < 2) ? $remainingDaysToExpire : 2],
                                        $domain->expiration,
                                        $details->fee,
                                        strip_tags(str_replace("<br/>", "\n", $payStr))
                                    );

                                    $msgHTML = sprintf('
                                        <p>Hi %s,<br/></p>
        
                                        <p>The %s (%s) service %s on <b>%s</b>.</p>
        
                                        <p>
                                            You\'re advised to submit a payment amount <b>UGX%s/=</b> as soon as possible to avoid service interruptions.<br/>
                                            Ignore this noification if you\'ve already submitted your payment.<br/>
                                        </p>
        
                                        <p><b>Choose how you like to send payment and send.</b></p>
                                        %s
                                        <p>
                                            <i><b>Note:</b> For urgent reinstatement of service, inform us by replying to this email with your payment receipt or transaction ID.</i><br/><br/>
                                            <i><b>Activation of expired services</b><br/>
                                            <b>40%%</b> extra charge after 30 days expiry.<br>
                                            <b>UGX250,000/=</b> extra charge after 60 days expiry.</i><br/>
                                        </p>
        
                                        <p>Thank you,<br/>Gagawala<br/>Management</p>
                                        ', 
                                        $details->customer->first_name,
                                        $details->category,
                                        $domain->name,
                                        $expireStatement[($remainingDaysToExpire < 2) ? $remainingDaysToExpire : 2],
                                        $domain->expiration,
                                        $details->fee,
                                        $payStr
                                    );

                                   $email = new \SendGrid\Mail\Mail();
                                   $email->setFrom('billing@gagawla.com', "Gagawala");
                                   $email->setSubject("Service Reminder");
                                   $email->addTo($details->customer->email, $details->customer->first_name);
                                   $email->addContent(
                                       "text/plain", $msgPlainText
                                   );
                                   $email->addContent(
                                       "text/html", $msgHTML
                                   );
                                   //$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
                                   $sendgrid = new \SendGrid('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');

                                   try {
                                       $response = $sendgrid->send($email);
                                       //print $response->statusCode() . "<br/>";
                                       //print_r($response->headers());
                                       //print $response->body() . "<br/>";

                                   } catch (Exception $e) {
                                       echo 'Caught exception: ',  $e->getMessage(), "<br/>";
                                   }

                                endif;
                            
                            endif;
                        endif;
                    endif;
                endforeach;
            endif;
        endif;
    endif;
endforeach;

//echo '<br/>Ending script';
