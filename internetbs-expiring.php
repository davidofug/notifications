<?php
require_once 'Helpers.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$remindInDays = [0,1,7,14,30,60];

$sqlInstructions = "SELECT * FROM payment_instructions WHERE status = 1";
$connection = dbConnect('mysql:host=localhost;dbname=gagraphi_demo', 'gagraphi_uza', 'jkEluFfan7dK');
$instructionRows = getRows($connection, $sqlInstructions, 'query');
$connection = null;

$instructions = [];

foreach( $instructionRows as $instructionRow ):
    $instructions[] = sprintf("<p><b>%s</b><br/> %s</p>",$instructionRow['method'], $instructionRow['instructions']);
endforeach;

$payStr = join($instructions,'');

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

        if(!$resultInternetbs->status) :

            if(is_array($resultInternetbs->domain) AND sizeof($resultInternetbs->domain)) :
    
                foreach($resultInternetbs->domain as $domain) :

                    if('Deleted' !== $domain->status) :

                        if('Expired' !== $domain->status) :

                            $today = date("Y/m/d");
                            $expiration = $domain->expiration;
                            $remainingDaysToExpire = dateDiff($today, $expiration);
                            
                            if($remainingDaysToExpire >= 0 AND $remainingDaysToExpire == $remainingDays) :
                                    
                                //printf("<p> {$domain->name} expires in {$remainingDays} status {$domain->status} expires on {$domain->expiration}</p>");
                                
                                $expireStatement = ['has expired','is expiring in 1 day',"will expire in {$remainingDays} days"];

                                $from = 'Gagawala <hello@gagawala.com>';

                                $headers = [
                                    'Content-type' => 'text/html; charset=iso-8859-1',
                                    'IME-Version' => '1.0',
                                    'From' => $from,
                                    'Reply-To' => $from,
                                    'X-Mailer' => 'PHP/' . phpversion()
                                ];


                                $domain_name = str_replace('.','-',$domain->name);

                                $sessionCRM = curlSession("https://gagawala.com/minicrm/wp-json/api/v1/product/$domain_name",'GET',[]);
                                $responseCRM = curl_exec($sessionCRM);
                                $errorCRM = curl_error($sessionCRM);

                                if ( $errorCRM ) :
                                    mail( "chat@david.ug", "Notifications cURL Error", "cURL Error #: {$err}" );
                                else:

                                    $details = json_decode($responseCRM);

                                    $msg = sprintf('
                                        <p>Hi %s,<br/></p>
        
                                        <p>The %s (%s) service %s on <b>%s</b>.</p>
        
                                        <p>
                                            You\'re advised to submit a payment amount <b>UGX%s/=</b> as soon as possible to avoid service interruptions.<br/>
                                            Ignore this noification if you\'ve already submitted your payment.<br/>
                                        </p>
        
                                        <p><b>Choose a preferred payment method below.</b></p>
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

                                    if( !mail($details->email, sprintf("%s notice", $details->category), $msg, $headers)) echo 'Notification not sent';

                                endif;
                            
                            endif;
                        endif;
                    endif;
                endforeach;
            endif;
        else:
            objectPrettyPrint($resultInternetbs);
        endif;
    endif;
endforeach;