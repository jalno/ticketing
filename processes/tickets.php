<?php
namespace packages\ticketing\processes;

use packages\base;
use packages\base\{log, Options, Process, Response};
use packages\ticketing\{Events, Logs, Ticket, Ticket_message};
use packages\userpanel\{Date, Log as UserpanelLog};

class Tickets extends Process {

	public function autoClose($data):response{
		$log = log::getInstance();
		$response = new response();
		$response->setStatus(false);
		$log->debug('looking for respite close time in options');
		$respiteTime = options::get('packages.ticketing.close.respitetime');
		$log->reply($respiteTime, "second ");
		$log->debug('setting time');
		$time = date::time();
		$log->reply($time);
		$closeTime = ($time - $respiteTime);
		$log->debug("looking in answered tickets that have last reply smaller than {$closeTime}");
		$ticket = new ticket();
		$ticket->where('status', ticket::answered);
		$ticket->where('reply_at', $closeTime, "<");
		$tickets = $ticket->get();
		$log->reply(count($tickets), " tickets found");
		foreach($tickets as $ticket){
			$log->debug('close ticket #', $ticket->id);
			$ticket->status = ticket::closed;
			$ticket->save();
			$log->debug("try send close notification trigger");
			$event = new events\tickets\close($ticket);
			$event->trigger();
			$log->reply('success');
		}
		$response->setStatus(true);

		return $response;
	}

	public function makeReplyLog($data) {
		Log::setLevel("debug");
		$log = Log::getInstance();
		$dryRun = (isset($data['dry-run']));
		if ($dryRun) {
			$log->info("dry-run mode: ON");
		}
		$checkExistBefore = (isset($data['check-exist-before']));
		if ($checkExistBefore) {
			$log->info("check this log is exist before: ON");
		}
		$log->info("get all tickets with messages");
		$tickets = new Ticket();
		$tickets->with("message");
		$tickets = $tickets->get();
		$log->reply(count($tickets), " Found!");
		$log->info("get each ticket messages to make reply log for it");
		foreach ($tickets as $ticket) {
			$ticketMessages = (new Ticket_message())->where("ticket", $ticket->id)->orderBy("date", "ASC")->get();
			$log->info("ticket: #" . $ticket->id . " has (" . count($ticket->message) . ") message");
			$firstMessage = true;
			foreach ($ticketMessages as $message) {
				$log->info("ticket: #" . $ticket->id . " message_id: #" . $message->id);
				if ($firstMessage) {
					$log->info("this is first message of ticket and is not reply message, skip...");
					$firstMessage = false;
					continue;
				}
				$userID = $message->user->id;
				if ($checkExistBefore) {
					$existBefore = (new UserpanelLog())
						->where("type", Logs\tickets\Reply::class)
						->where("user", $userID)
						->where("time", $message->date)
						->has();
					if ($existBefore) {
						$log->info("check-exist-before is ON, this log is exist before, skip...");
						continue;
					}
				}
				$getLastLogIP = (new UserpanelLog())
					->where("user", $userID)
					->where("time", $message->date, "<")
					->orderBy("time", "DESC")
					->getValue("ip");
				$userpanelLog = new UserpanelLog();
				$userpanelLog->user = $message->user;
				$userpanelLog->time = $message->date;
				$userpanelLog->ip = $getLastLogIP;
				$userpanelLog->title = t("ticketing.logs.reply", array("ticket_id" => $ticket->id));
				$userpanelLog->type = Logs\tickets\Reply::class;
				if (!$dryRun) {
					$userpanelLog->save();
					$log->info("reply log created, Log id: #" . $userpanelLog->id);
				} else {
					$log->debug("dry-run: reply log (!)created");
				}
			}
		}
	}
}