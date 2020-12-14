export default interface IFormAjaxError {
	input?: string;
	error: "data_duplicate" | "data_validation" | "unknown" | string;
	type: "fatal" | "warning" | "notice";
	code?: string;
	message?: string;
	data?: {[key: string]: string};
}