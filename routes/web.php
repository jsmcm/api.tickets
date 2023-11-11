<?php
use App\Models\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Jobs\TicketCreatedEmail;
use App\Mail\TicketCreated;
use App\Models\Department;
use Illuminate\Support\Facades\Log;
use App\Services\MailDownloader\Download;
use Illuminate\Support\Facades\Cache;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get("/uname", function () {
return `uname -a`;
});

Route::get("/mbstring", function () {
    return extension_loaded("mbstring") ? "ok" : "ko";
});

Route::get("/imap", function () {
    return extension_loaded("imap") ? "ok" : "ko";
});

Route::get("/php", function () {
    phpinfo();
});

Route::get("/users", function () { 

    return;
	print "<p>getting users no cache...</p>";

    //$users = cache()->remember("aaa", now()->addSeconds(30), function () {
        $users = User::all();
    //});

	
	print_r($users);
});

Route::get("/env", function() {
	print "<p>db: ".env("DB_DATABASE")."</p>";
	print "<p>db: ".env("DB_HOST")."</p>";
});


Route::get("/download", function () {
   
    //$download = new Download("john@fluffykids.co.za", "M4thewMc05", App\Jobs\MakeTicketFromEmail::class, "mail.fluffykids.co.za", 143,"imap");

    //$download->download();
    

});


Route::get('/', function () {
    return "<a href='https://softsmart.co.za'>SoftSmart.co.za</a>";
});


Route::get("/smtp", function() {
return;
    // MAIL_MAILER=smtp
    // MAIL_HOST=mail.softsmart.co.za
    // MAIL_PORT=587
    // MAIL_USERNAME=john@softsmart.co.za
    // MAIL_PASSWORD=ThisIsC00l7533
    // MAIL_ENCRYPTION=starttls
    // MAIL_FROM_ADDRESS="john@softsmart.co.za"
    // MAIL_FROM_NAME="Hello Tickets"

    $departments = \App\Models\Department::orderBy("id", "desc")->get();

    print "Departments: <br/>";
    print_r($departments);
    print "<p><hr></p>";

    foreach ($departments as $department) {
        
        if ($department->mail_host === "mail.softsmart.co.za") {
            continue;
        }
        print "<p>Department: ".print_r($department->mail_host, true)."</p>";
        Log::write("debug", "in web.php, ".$department->mail_host);
        TicketCreatedEmail::dispatch($department, "john@softsmart.co.za", "This is a subject", 12344);

    }

    return "done";
    
});





Route::get("/id", function () {

	return;

    $mail_boxes = config("support.mail_boxes");
    print "mail_boxes: ".print_r($mail_boxes, true);

    // [#000f3] How to Enter

    $subject = "[#000f3] How to Enter";

    if(preg_match ("/[[][#][a-fA-F0-9]{1,15}[]]/",$subject,$regs)) {

        
        $TicketID = substr($regs[0], 2);
        $TicketID = substr($TicketID, 0, strlen($TicketID) - 1);
        $TicketID = substr($TicketID, strpos($TicketID, "-") + 1);
        $TicketID = hexdec($TicketID);

    }
    print "<p><b>TicketID: </b>".$TicketID."</p>";






});

Route::get("/mail", function () {

	return;

    $departments = \App\Models\Department::orderBy("id", "desc")
    ->where(["deleted"=>false])
    ->get();

    foreach ($departments as $department) {

        print "<p>in web.php, ".$department->department.", ".$department->mail_host.", ".$department->pop_port.", ".$department->mail_username.", ".$department->mail_password."</p>";
         

        print "<p>host: '".$department->mail_host."'</p>";
        print "<p>pop_port: '".$department->pop_port."'</p>";
        print "<p>connection: {".$department->mail_host.":".$department->pop_port."/imap/notls/novalidate-cert}INBOX</p>";
        print "<p>userName: '".$department->mail_username."'</p>";
        print "<p>password: '".$department->mail_password."'</p>";
        

        print "<p>connection: {".config('support.host').":".config('support.port')."/".config('support.protocol')."/notls/novalidate-cert}INBOX</p>";


        // Create PhpImap\Mailbox instance for all further actions
        $mailbox = new PhpImap\Mailbox(
            '{'.$department->mail_host.':'.$department->pop_port.'/imap/notls/novalidate-cert}INBOX', // IMAP server and mailbox folder
            $department->mail_username, // Username for the before configured mailbox
            $department->mail_password, // Password for the before configured username
            __DIR__, // Directory, where attachments will be saved (optional)
            'UTF-8', // Server encoding (optional)
            true, // Trim leading/ending whitespaces of IMAP path (optional)
            false // Attachment filename mode (optional; false = random filename; true = original filename)
        );


        // set some connection arguments (if appropriate)
        $mailbox->setConnectionArgs(
            CL_EXPUNGE // expunge deleted mails upon mailbox close
        );

        try {
            // Get all emails (messages)
            // PHP.net imap_search criteria: http://php.net/manual/en/function.imap-search.php
            $mailsIds = $mailbox->searchMailbox("ALL");
        } catch(PhpImap\Exceptions\ConnectionException $ex) {
            echo "IMAP connection failed: " . implode(",", $ex->getErrors('all'));
            die();
        }

        // If $mailsIds is empty, no emails could be found
        if(!$mailsIds) {
            //die('Mailbox is empty');
            print "<p>Its empty</p>";
            continue;
        } else {
            print "<p>its not empty</p>";
        }

        // Get the first message
        // If '__DIR__' was defined in the first line, it will automatically
        // save all attachments to the specified directory

        print "<p>count: ".count($mailsIds)."</p>";

        foreach ($mailsIds as $mailId) {

            
            $mail = $mailbox->getMail($mailId);

            $header = $mailbox->getMailHeader($mailId)->headersRaw;

            $ip_matches = [];
            preg_match_all('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $header, $ip_matches);


            $ipCounter = 0;
            foreach ($ip_matches[0] as $ip) {
                
                $ip = filter_var(
                    $ip, 
                    FILTER_VALIDATE_IP, 
                    FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE |  FILTER_FLAG_NO_RES_RANGE
                );

                if ($ip !== false) {
                    if ($ipCounter == 0) {
                        $allIps[] = $ip;
                    } else {
                        //$allIps[] = $ip."_maybe_not";
                    }

                    $ipCounter++;
                }
            
            }

            $allIps = array_unique($allIps);

            print "<p>allIps</p>";
            print_r($allIps);
            print "<p></p>";


            $fromName = (string) (isset($mail->fromName) ? $mail->fromName : $mail->fromAddress);
            print "<p><b>fromName: </b>".$fromName."</p>";

            $fromEmailAddress = (string) $mail->fromAddress;
            print "<p><b>fromEmailAddress: </b>".$fromEmailAddress."</p>";

            $user = User::where(["email" => $fromEmailAddress])->first();
            if ($user == null) {
                
                $firstName = "";
                if (isset($fromName)) {
                    $firstName = $fromName;
                }

                $user = new User();
                $user->level = 1;
                $user->name = $firstName;
                $user->email = $fromEmailAddress;
                $user->password = Hash::make(date("Y-m-d H:i:s").mt_rand(10000, 99999).mt_rand(10000, 99999));
                $user->save();

            }

            print "<p><b>user:</b> ".print_r($user->name, true)."</p>";

            $subject = (string) $mail->subject?(string) $mail->subject:'[No Subject]';
            print "<p><b>subject: </b>".$subject."</p>";



            $TicketID = 0;
            //print "Checking if id present in '".$Subject."'<br>";
            if(preg_match ("/[[][#][a-fA-F0-9]{1,15}[]]/",$subject,$regs)) {


                $TicketID = substr($regs[0], 2);

                print "<p><b>TicketID 1: </b>".$TicketID."</p>";
                $TicketID = substr($TicketID, 0, strlen($TicketID) - 1);
                print "<p><b>TicketID 5: </b>".$TicketID."</p>";
                $TicketID = hexdec($TicketID);

            }
            print "<p><b>TicketID 10: </b>".$TicketID."</p>";





            // Show, if $mail has one or more attachments
            // echo "<p>Mail has attachments? ";
            // if($mail->hasAttachments()) {
            //     echo "Yes<p>";
            // } else {
            //     echo "No<p>";
            // }

            // Print all information of $mail
            print_r($mail);

            // Print all attachements of $mail
            echo "<p><p>Attachments:<p>";
            print_r($mail->getAttachments());

            echo "<p><p>Message:<p>";
            $message = "";
            if ($mail->textHtml) {
                echo "Message HTML:<p>".$mail->textHtml;
                $message = $mail->textHtml;
            } else {
                echo "Message Plain:<p>".$mail->textPlain;
                $message = $mail->textPlain;
            }



            $ToArray = array();
            $sentTo = "";

            if(isset($mail->to)) {

                foreach($mail->to as $emailAddress=>$toName) {

                    //if( ! in_array($emailAddress, $AllDepartmentEmailAddressArray)) {

                        array_push($ToArray, $emailAddress);

                    //} else {
                        
                        //$sentTo = $emailAddress;

                    //}

                } 

            }  


            if(isset($mail->cc)) {

                foreach($mail->cc as $emailAddress=>$ccName) {
                    
                    //if( ! in_array($emailAddress, $AllDepartmentEmailAddressArray)) {

                        array_push($ToArray, $emailAddress);

                    //}

                } 

            }  


            print "<p><b>ToArray: </b>".print_r($ToArray, true)."</p>";

            //$mailbox->deleteMail($mailsIds[0]);
            

            $priority = "normal";
            $departmentId = $department->id;
            $ticket = new \App\Http\Controllers\TicketController();
            try {
                $ticketId = $ticket->store($departmentId, $user->id, $subject, $allIps[0], $priority);
            } catch (Exception $e) {
                return response()->json(["status" => "failed", "data" => "ticket creation failed:".$e->getMessage()], 500);
            }

            $type = "from-client";
            print "<p><b>ticketId: </b>".$ticketId."</p>";


            $thread = new \App\Http\Controllers\ThreadController();
            try {
                print "<p>Creating Thread</p>";
                print "<p><b>ticketId: </b>".$ticketId."</p>";
                print "<p><b>type: </b>".$type."</p>";
                print "<p><b>message: </b>".$message."</p>";

                $threadId = $thread->store($ticketId, $type, $message);
                print "<p><b>threadId: </b>".$threadId."</p>";

            } catch (Exception $e) {
                print "<p>Error: ".$e->getMessage()."</p>";
                return response()->json(["status" => "failed", "data" => "ticket thread creation failed"], 500);
            }

            print "<p>After Creating Thread</p>";
            print "<p><b>threadId 1: </b>".$threadId."</p>";

            if ($threadId) {

                print "<p>sending email...</p>";
                SendEmail::dispatch(
                    $department,
                    $fromEmailAddress,
                    $subject,
                    $ticketId
                );
                
            }

                    
            print "<p><hr></p>";
        }

    }

    return "done reading...";
});
