import Controller from './abstract_controller';

export default class extends Controller {

	static targets = ['element', 'issue']

	connect() {
		// this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/hello_controller.js';
	}

	readItemIssue(event) {

		console.log('%creadItemIssue', 'font-size: 20px');

		const button = event.target;
		const issueId = button.dataset.issueId;
		const issue = this.issueTargets.find(e => e.dataset.id === issueId)
		const volumeId = issue.dataset.volumeId;
		const read = Number.parseInt(issue.dataset.read);

		const issueList = issue.closest('.item-issues')

		const item = issue.closest('.item')
		const itemId = item.dataset.id

		// const item = issueList.

		console.log('issueList', issueList)
		console.log({
			button: button,
			issueId: issueId,
			issue: issue,
			read: read,
			event: event,
		})

		event.preventDefault();

		// todo : itemListLoader: on

		this.readIssue(event, issueId, read, volumeId, itemId)

	}
}