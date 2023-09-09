import Managements from "./Settings/Departments/Managements";
import Templates from "./Settings/Templates";

export default class Settings{
	public static initIfNeeded(){
		Managements.initIfNeeded();
		Templates.initIfNeeded();
	}
}