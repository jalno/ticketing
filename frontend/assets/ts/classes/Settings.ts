import Managements from "./Settings/Departments/Managements";
import Templates from "./Settings/Templates";
import Labels from "./Settings/Labels";

export default class Settings{
	public static initIfNeeded(){
		Managements.initIfNeeded();
		Templates.initIfNeeded();
		Labels.initIfNeeded();
	}
}