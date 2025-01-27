<?php

namespace Tests\Feature;

use App\Jobs\TicketCreatedEmail;
use App\Models\Department;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TicketServiceTest extends TestCase
{

    public function test_store_throws_exception_if_department_not_set(): void
    {
        $ticket = new TicketService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Department Id not set");
        $this->expectExceptionCode(1600001);

        $ticket->store(
            "TEST SUBJECT",
            0,
            "127.0.0.1",
            "normal",
            "test@example.com",
            "Joe"
        );

    }


    public function test_store_throws_exception_if_email_not_set(): void
    {
        $ticket = new TicketService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Email not set");
        $this->expectExceptionCode(1600002);

        $ticket->store(
            "TEST SUBJECT",
            1,
            "127.0.0.1",
            "normal",
            "",
            "Joe"
        );

    }

    public function test_can_store_ticket(): void
    {

        $department = new Department();
        $department->user_id = 2;
        $department->department = "test_can_store".date("Ymd_His");
        $department->logo_url = "test_can_store";
        $department->signature = "test_can_store";
        $department->mail_host = "test_can_store";
        $department->mail_username = "test_can_store";
        $department->mail_password = "test_can_store";
        $department->email_address = "test_can_store@example.com";
        $department->api_token = "test_can_store";
        $department->api_base_url = "test_can_store";
        $department->save();


        Queue::fake();
        
        $ticketService = new TicketService();
        $ticket = $ticketService->store(
            "test_can_store_ticket SUBJECT",
            $department->id,
            "127.0.0.1",
            "normal",
            "test_can_store_ticket@example.com",
            "Joe"
        );

        $this->assertInstanceOf(Ticket::class, $ticket);
        $this->assertEquals("test_can_store_ticket SUBJECT", $ticket->subject);
        $this->assertEquals("test_can_store_ticket@example.com", $ticket->user->email);

        
        // Assert that the job was dispatched
        Queue::assertPushed(TicketCreatedEmail::class, function ($job) use ($department, $ticket) {
            return $job->department->id === $department->id &&
                   $job->email === $ticket->user->email &&
                   $job->subject === $ticket->subject &&
                   $job->ticketId === $ticket->id;
        });

  

    }
        

}
