<?php

namespace Tests\Feature;

use App\Jobs\ThreadReplyCreatedEmail;
use App\Models\Thread;
use App\Models\Ticket;
use App\Services\ThreadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ThreadServiceTest extends TestCase
{

    public function test_store_throws_exception_if_type_blank(): void
    {
        $randomSubject = "TEST SUBJECT - ".date("Ymd_His")." - ".mt_rand(10000, 99999);
        $randomMessage = "This is a message".date("Ymd_His")." - ".mt_rand(10000, 99999);

        $ticket = new Ticket();
        $ticket->department_id = 1;
        $ticket->user_id = 2;
        $ticket->subject = $randomSubject;
        $ticket->status = "open";
        $ticket->ip = "";
 

        $threadService = new ThreadService();
    
        $ticket->id = mt_rand(1000, 10000);

        $random = (string)mt_rand(10000,99999);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Thread type not set");
        $this->expectExceptionCode(1600001);

        $returned = $threadService->store(
            $ticket,
            "",
            $randomMessage,
            $random,
            true,
            ""
        );
    }
    
    public function test_store_throws_exception_if_message_blank(): void
    {

        $randomSubject = "TEST SUBJECT - ".date("Ymd_His")." - ".mt_rand(10000, 99999);
        $randomMessage = "";

        $ticket = new Ticket();
        $ticket->department_id = 1;
        $ticket->user_id = 2;
        $ticket->subject = $randomSubject;
        $ticket->status = "open";
        $ticket->ip = "";
 

        $threadService = new ThreadService();
    
        $ticket->id = mt_rand(1000, 10000);

        $random = (string)mt_rand(10000,99999);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Message is blank");
        $this->expectExceptionCode(1600002);

        $returned = $threadService->store(
            $ticket,
            "from-client",
            $randomMessage,
            $random,
            true,
            ""
        );

    }
    
    public function test_can_store_thread(): void
    {

        $randomSubject = "TEST SUBJECT - ".date("Ymd_His")." - ".mt_rand(10000, 99999);
        $randomMessage = "This is a message".date("Ymd_His")." - ".mt_rand(10000, 99999);

        $ticket = new Ticket();
        $ticket->department_id = 1;
        $ticket->user_id = 2;
        $ticket->subject = $randomSubject;
        $ticket->status = "open";
        $ticket->ip = "";
 

        $threadService = new ThreadService();
    
        $ticket->id = mt_rand(1000, 10000);

        $random = (string)mt_rand(10000,99999);
        
        $returned = $threadService->store(
            $ticket,
            "from-client",
            $randomMessage,
            $random,
            true,
            ""
        );

        $this->assertInstanceOf(Thread::class, $returned);
    
        $this->assertEquals($randomMessage, $returned->message);
        $this->assertEquals("from-client", $returned->type);
    }


        
    public function test_reply_email_dispatched(): void
    {

        $randomSubject = "TEST SUBJECT - ".date("Ymd_His")." - ".mt_rand(10000, 99999);
        $randomMessage = "This is a message".date("Ymd_His")." - ".mt_rand(10000, 99999);

        $ticket = new Ticket();
        $ticket->department_id = 1;
        $ticket->user_id = 2;
        $ticket->subject = $randomSubject;
        $ticket->status = "open";
        $ticket->ip = "";
 

        Bus::fake();

        $threadService = new ThreadService();
    
        $ticket->id = mt_rand(1000, 10000);

        $random = (string)mt_rand(10000,99999);
        
        $returned = $threadService->store(
            $ticket,
            "from-client",
            $randomMessage,
            $random,
            false,
            ""
        );

        $this->assertInstanceOf(Thread::class, $returned);
    
        $this->assertEquals($randomMessage, $returned->message);
        $this->assertEquals("from-client", $returned->type);

        Bus::assertDispatched(ThreadReplyCreatedEmail::class, function($job) use ($returned) {
            return $returned === $job->thread;
        });

    }




        
    public function test_reply_email_not_dispatched(): void
    {

        $randomSubject = "TEST SUBJECT - ".date("Ymd_His")." - ".mt_rand(10000, 99999);
        $randomMessage = "This is a message".date("Ymd_His")." - ".mt_rand(10000, 99999);

        $ticket = new Ticket();
        $ticket->department_id = 1;
        $ticket->user_id = 2;
        $ticket->subject = $randomSubject;
        $ticket->status = "open";
        $ticket->ip = "";
 

        Bus::fake();

        $threadService = new ThreadService();
    
        $ticket->id = mt_rand(1000, 10000);

        $random = (string)mt_rand(10000,99999);
        
        $returned = $threadService->store(
            $ticket,
            "from-client",
            $randomMessage,
            $random,
            true,
            ""
        );

        $this->assertInstanceOf(Thread::class, $returned);
    
        $this->assertEquals($randomMessage, $returned->message);
        $this->assertEquals("from-client", $returned->type);

        Bus::assertNotDispatched(ThreadReplyCreatedEmail::class);

    }


}
