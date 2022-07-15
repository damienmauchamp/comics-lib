import {Controller} from '@hotwired/stimulus';

export default class extends Controller {

	connect() {

	}

	/** UTILS */

	isHomepage() {
		return !!document.querySelector('section#nextToReadStarted');
	}

	/** HOMEPAGE */

	/**
	 * Updates volume to display the next issue or remove it if complete
	 * @param event
	 * @param volume
	 * @param data
	 * @returns {boolean}
	 */
	setVolumeNextToReadIssue(event, volume, data) {

		if (!data.next) {
			if (this.isHomepage()) {
				// we're on the homepage, so we remove the completed volume
				volume.remove();
			} else {
				volume.classList.add('done');
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
	}

	moveVolumeToNextToRead(event, volume) {

		if (!this.isHomepage()) {
			// skipping
			return;
		}

		// Moving the volume to the nextToReadStarted section
		const nextToReadStarted = document.querySelector('section#nextToReadStarted'),
			nextToReadNotStarted = document.querySelector('section#nextToReadNotStarted');
		// const isHomepage = !!nextToReadStarted;

		if (nextToReadNotStarted.contains(volume)) {
			// volume is just being started
			// we move it first to the nextToReadStarted section
			const list = nextToReadStarted.querySelector('.volumes-list');
			// nextToReadStarted.appendChild(volume);
			list.insertBefore(volume, list.firstChild);
		}

	}

	/** VOLUME PAGE */

	setVolumeIssueRead(event, issueId, was_read) {
		const section = document.querySelector('section#volume-issues');
		if (!section) {
			return;
		}

		const issue = section.querySelector(`.issue[data-id="${issueId}"]`);
		issue.dataset.read = was_read ? '0' : '1';
		issue.classList.toggle('read', !was_read);
	}
}