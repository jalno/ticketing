import * as $ from "jquery";
import Ticket from "./classes/Ticket";
import Settings from "./classes/Settings";
import Editor from "./classes/Editor";

$(function(){
	Ticket.initIfNeeded();
	Settings.initIfNeeded();
	Editor.initIfNeeded();
});