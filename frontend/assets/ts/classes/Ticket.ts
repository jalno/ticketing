import List from "./Ticket/List";
import Add from "./Ticket/Add";
import Reply from "./Ticket/Reply";
import Edit from "./Ticket/Edit";
export default class Ticket{
	public static initIfNeeded(){
		List.initIfNeeded();
		Add.initIfNeeded();
		Reply.initIfNeeded();
		Edit.initIfNeeded();
	}
	
}