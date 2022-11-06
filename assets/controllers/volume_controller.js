import Controller from './abstract_controller';

export default class extends Controller {

	static targets = ['element']

	connect() {
		// this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/hello_controller.js';
	}

	read(event) {

		const button = event.target;
		const volumeId = button.dataset.volumeId;
		const volume = this.elementTargets.find(e => e.dataset.id === volumeId)

		console.log('button', button);
		console.log('volumeId', volumeId);
		console.log('volume', volume)
		// console.log('this.targets....', this.targets.findAll('element'));
		// console.log('this.elementTargets', this.elementTargets);

		const issueId = volume.dataset.issueId,
			issueNumber = volume.dataset.issueNumber;

		console.log({
			volumeId: volumeId,
			issueId: issueId,
			issueNumber: issueNumber,
		})

		event.preventDefault();

		const url = `/issue/${issueId}/read`;
		// const url = `/volume/${volumeId}/issue/${issueNumber}/read`;
		const form = new FormData();
		// form.append('volumeId', volumeId);
		// form.append('issueId', issueId);
		// form.append('issueNumber', issueNumber);

		// adding a loader
		const loader = document.createElement('div'),
			loaderIcon = document.createElement('i');
		loader.className = 'loader';
		loaderIcon.className = 'fa-spin fa-solid fa-circle-notch';
		// loaderIcon.attributes['aria-hidden'] = 'true';
		loader.appendChild(loaderIcon);

		const volumeImg = volume.querySelector('.volume-img');
		volumeImg.prepend(loader);
		volumeImg.classList.add('loading');

		fetch(url, {
			method: 'POST',
			body: form
		}).then(response => {
			if (response.ok) {
				console.log('response', response)
				return response.json();
			}
			throw new Error('Network response was not ok.');
		}).then(json => json.data).then(data => {

			// marking as read
			this.setVolumeNextToReadIssue(event, volume, data);

			if (!this.isHomepage()) {
				// mark issue as read if we're on the volume page since it'd be listed
				this.setVolumeIssueRead(event, issueId, false);
			}

			return data;

		}).then(data => {

			// moving it to "Next to Read" section if nedded
			this.moveVolumeToNextToRead(event, volume);

			return data;
		}).then(data => {

			// todo : handle read/not read ?
			// checking if the issue read is in one of the issue list
			const sectionNotStartedItems = document.querySelector('section#nextToReadNotStartedItems');
			let itemIssuesNotStarted = Array.from(sectionNotStartedItems.querySelectorAll('.item-issue'))
				.filter(e => e.dataset.id === String(issueId));
			console.log('itemIssuesNotStarted', itemIssuesNotStarted)
			if (itemIssuesNotStarted) {
				itemIssuesNotStarted.forEach(itemIssue => {
					let item = itemIssue.closest('.item');
					// moving the item in the next to read
					this.moveItemToNextToRead(event, item);
				})
			}

			// marking issue as read
			const itemIssuesSection = document.querySelector('section#nextToReadStartedItems');
			let itemIssues = Array.from(itemIssuesSection.querySelectorAll('.item-issue'))
				.filter(e => e.dataset.id === String(issueId));
			console.log('itemIssues', itemIssues)
			if (itemIssues) {
				itemIssues.forEach(itemIssue => {
					// marking it as read
					itemIssue.classList.add('read');
					// let item = itemIssue.closest('.item');
					// // moving the item in the next to read
					// this.moveItemToNextToRead(event, item);
				})
			}
			// issueId
			// itemIssuesSection.querySelectorAll('.item-issue')
			// let itemIssue = Array.from(itemIssuesSection.querySelectorAll('.item-issue'))
			// 	.find(e => e.dataset.id === String(volumeId));
			// item-issue

			return data;
		}).catch(error => {
			console.log(error);
		});
		// issue-id
		// issue-number

		// console.log('event, element', element, volumeId, element.dataset)
		// console.log(volume_id, element.dataset)

		// console.log(element, event.currentTarget)
		// }
		//
		// read(event) {
		// 	const element = event.target
		// 	console.log(element, event.currentTarget)

		// const element = this.elementTarget;
		// console.log('this.elementTarget', this.elementTarget);
		// console.log('this.elementTargets', this.elementTargets);
		// console.log('this.targets', this.targets);


		//
		// const volume_id = this.volume_idTarget.value;
		// const issue_id = this.issue_idTarget.value;
		// const issue_number = this.issue_numberTarget.value;
		// console.log(volume_id, issue_id, issue_number);
	}
}
