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
		const loader = volume.querySelector('.loader'),
			volumeImg = volume.querySelector('.volume-img'),
			volumeDetails = volume.querySelector('.volume-details');

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

		// image
		const img = volumeImg.querySelector('img');
		img.onload = () => {
			if (loader) {
				loader.remove();
			}
			volumeImg.classList.remove('loading');
		};
		img.src = data.next.image;
		img.alt = data.next.name;

		// title
		volumeDetails.querySelector('.title h3 a').textContent = data.next.volume_name;

		// progress bar
		const progressBar = volumeImg.querySelector('.progress-bar');
		try {
			progressBar.classList.toggle('complete', data.volume.done);
			progressBar.classList.toggle('uncomplete', !data.volume.done);
			progressBar.innerHTML = `${data.volume.progress}%`;
			progressBar.style.width = `${data.volume.progress}%`;
			progressBar.attributes['aria-valuenow'].value = data.volume.progress;
		} catch (e) {
			console.error('[PROGRESS BAR] Erreur', e, progressBar)
		}

		// remaining
		const remaining = volumeDetails.querySelector('.remaining')
		try {
			if (remaining) {
				remaining.textContent = data.volume.remaining.text;
				remaining.dataset.read = data.volume.remaining.read;
				remaining.dataset.total = data.volume.remaining.total;
			}
		} catch (e) {
			console.error('[REMAINING] Erreur', e, remaining)
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