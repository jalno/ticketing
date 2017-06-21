import List from "./Ticket/List";
import Add from "./Ticket/Add";
export default class Ticket{
	public static initIfNeeded(){
		List.initIfNeeded();
		Add.initIfNeeded();
	}
	
}