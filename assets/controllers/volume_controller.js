import {Controller} from '@hotwired/stimulus';

export default class extends Controller {

	static targets = ['element']

	connect() {
		// this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/hello_controller.js';
	}

	read(event) {

		const button = event.target;
		const volumeId = button.dataset.volumeId;
		const volume = this.elementTargets.find(e => e.dataset.id === volumeId)

		// console.log('button', button);
		// console.log('volumeId', volumeId);
		// console.log('volume', volume)
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

		fetch(url, {
			method: 'POST',
			body: form
		}).then(response => {
			if (response.ok) {
				console.log('response', response)
				return response.json();
			}
			throw new Error('Network response was not ok.');
		}).then(json => {

			const data = json.data;

			const nextToReadStarted = document.querySelector('section#nextToReadStarted'),
				isHomepage = !!nextToReadStarted;

			if (!isHomepage) {
				// todo : mark issue as read if we're on the volume page since it'd be listed
			}

			if (!data.next) {
				if (nextToReadStarted) {
					volume.remove();
				} else {
					// todo : volume done
					volume.innerHTML = '<div>Up to date</div>';
				}
				return true;
			}

			volume.dataset.issueId = data.next.id;
			volume.dataset.issueNumber = data.next.number;

			// todo : progress

			// title
			volume.querySelector('.volume-details .title h3 a').textContent = data.next.volume_name;

			// img
			const img = volume.querySelector('.volume-img img');
			img.src = data.next.image;
			img.alt = data.next.name;

			// remaining
			const remaining = volume.querySelector('.volume-details .remaining');
			try {
				remaining.textContent = data.next.remaining.text;
				remaining.dataset.read = data.next.remaining.read;
				remaining.dataset.total = data.next.remaining.total;
			} catch (e) {

			}

			// volume.innerHTML = data.next.html;

			return data;
		}).then(data => {

			// Moving the volume to the nextToReadStarted section

			const nextToReadStarted = document.querySelector('section#nextToReadStarted'),
				nextToReadNotStarted = document.querySelector('section#nextToReadNotStarted');
			const isHomepage = !!nextToReadStarted;

			if (!isHomepage) {
				// skipping
				return data;
			}

			if (nextToReadNotStarted.contains(volume)) {
				// volume is just being started
				// we move it first to the nextToReadStarted section
				const list = nextToReadStarted.querySelector('.volumes-list');
				// nextToReadStarted.appendChild(volume);
				list.insertBefore(volume, list.firstChild);

			}
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
