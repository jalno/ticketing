import * as $ from "jquery";
import Ticket from "./classes/Ticket";
import Settings from "./classes/Settings";
$(function(){
	Ticket.initIfNeeded();
	Settings.initIfNeeded();
});