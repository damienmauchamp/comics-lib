import {Controller} from '@hotwired/stimulus';

export default class extends Controller {

	connect() {

	}

	/** UTILS */

	getCurrentPage() {
		try {
			// return document.querySelector('nav.top li.selected').dataset.page
			return document.querySelector('nav.top').dataset.page;
		} catch (e) {
			console.error(e);
			return null;
		}
	}

	isHomepage() {
		return this.getCurrentPage() === 'home';
	}

	isComicsPage() {
		return this.getCurrentPage() === 'comics';
	}

	ajax(method, url, data = {}) {
		return new Promise((resolve, reject) => {
			$.ajax({
				url: url,
				type: method,
				data: data,
				success: function (data) {
					resolve(data)
				},
				error: function (error) {
					reject(error)
				},
			})
		})
	}

	get(url) {
		return this.ajax('GET', url);
	}

	post(url, data) {
		return this.ajax('POST', url, data);
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
		console.log('%csetVolumeNextToReadIssue', 'font-size: 20px');

		const loader = volume.querySelector('.loader'),
			volumeImg = volume.querySelector('.volume-img'),
			volumeDetails = volume.querySelector('.volume-details');

		const nextToReadVolume = document.querySelector('section#volume-next-to-read');
		if (!data.next) {
			if (this.isHomepage() || this.isComicsPage()) {
				this.moveVolumeToUpToDate(event, volume, data)
			} else {
				if (nextToReadVolume) {
					// where's on the volume page
					// hiding the Next to Read section
					nextToReadVolume.hidden = true;
					return true;
				}
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

		//
		if (nextToReadVolume) {
			// where's on the volume page
			// displaying the Next to Read section
			nextToReadVolume.hidden = false;
			return true;
		}
	}

	moveVolumeToNextToRead(event, volume) {
		console.log('%cmoveVolumeToNextToRead', 'font-size: 20px');

		if (!this.isHomepage() && !this.isComicsPage()) {
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

	moveVolumeToUpToDate(event, volume, data = null) {
		console.log('%cmoveVolumeToUpToDate', 'font-size: 20px');

		if (!this.isHomepage() && !this.isComicsPage()) {
			// skipping
			return;
		}

		// Moving the volume to the upToDate section
		const nextToReadStarted = document.querySelector('section#nextToReadStarted'),
			upToDate = document.querySelector('section#upToDate');

		if (data) {

			// Creating a new element with the new html
			let newVolume = document.createElement("div");
			newVolume.innerHTML = data.volume.html;
			console.log('setVolumeNextToReadIssue->moveVolumeToUpToDate newVolume:', newVolume)

			//
			const list = upToDate.querySelector('.volumes-list');
			list.insertBefore(newVolume.firstElementChild, list.firstChild);

			// removing the volume from the old list
			volume.remove();

			return;
		}

		if (nextToReadStarted.contains(volume)) {
			// volume is just being started
			// we move it first to the nextToReadStarted section
			const list = upToDate.querySelector('.volumes-list');
			// nextToReadStarted.appendChild(volume);
			list.insertBefore(volume, list.firstChild);
		}
	}

	updateItemList(event, item) {
		console.log('%cupdateItemList', 'font-size: 20px');
		console.log('todo : this.updateItemList(event, item);', {item: item})
		// todo : itemListLoader: off + if volume on the page : update left (ou move to next to read)
	}

	moveItemToNextToRead(event, item) {
		console.log('%cmoveItemToNextToRead', 'font-size: 20px');

		if (!this.isHomepage() && !this.isComicsPage()) {
			// skipping
			return;
		}

		// Moving the item to the nextToReadStarted section
		const nextToReadStarted = document.querySelector('section#nextToReadStartedItems'),
			nextToReadNotStarted = document.querySelector('section#nextToReadNotStartedItems');

		if (nextToReadNotStarted.contains(item)) {
			// item is just being started
			// we move it first to the nextToReadStarted section
			const list = nextToReadStarted.querySelector('.items-list');
			list.insertBefore(item, list.firstChild);
		}
	}

	/**
	 *
	 * @param event
	 * @param issueId
	 * @param read TRUE if is already read
	 * @param {Number|undefined} volumeId
	 * @param {Number|undefined} itemId
	 */
	readIssue(event, issueId, read, volumeId, itemId) {

		console.log('%creadIssue', 'font-size: 20px');

		// const issueId = button.dataset.issueId;
		const readUnread = read ? 'unread' : 'read';
		const url = `/issue/${issueId}/${readUnread}`;
		const form = new FormData();

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

			console.log('presetVolumeIssueRead', issueId, read);
			this.setVolumeIssueRead(event, issueId, read);

			return data;

		}).then(data => {

			// VOLUME PAGE : if read is in the "Next to read" section, update the next to read section
			const volumePageSectionNextToRead = document.querySelector('section#volume-next-to-read');
			if (volumePageSectionNextToRead) {
				const volume = volumePageSectionNextToRead.querySelector('div.volume');
				// this.setNextToRead(section, data);
				this.setVolumeNextToReadIssue(event, volume, data);
			}

			// if volume is in "Not started" and it started
			const sectionNextToRead = document.querySelector('section#nextToReadStarted');
			const sectionNotStarted = document.querySelector('section#nextToReadNotStarted');
			if (sectionNotStarted && volumeId) {
				let volume = Array.from(sectionNotStarted.querySelectorAll('div.volume'))
					.find(e => e.dataset.id === String(volumeId));
				if (volume) {
					this.moveVolumeToNextToRead(event, volume);

					// todo : then update the volume
					volume = Array.from(sectionNextToRead.querySelectorAll('div.volume'))
						.find(e => e.dataset.id === String(volumeId));
					console.log('todo : then update the volume', volume)
					// todo : this.updateVolume(event, volume)
				}
			} else if (sectionNextToRead && volumeId) {
				// todo : then update the volume
				let volume = Array.from(sectionNextToRead.querySelectorAll('div.volume'))
					.find(e => e.dataset.id === String(volumeId));
				console.log('todo : then update the volume', volume)
				// todo : this.updateVolume(event, volume)
			}

			const itemIssuesSection = document.querySelector('section#nextToReadStartedItems');
			const sectionNotStartedItems = document.querySelector('section#nextToReadNotStartedItems');

			console.log('toooooooo')
			console.log('itemIssuesSection', itemIssuesSection)
			console.log('sectionNotStartedItems', sectionNotStartedItems)
			console.log('itemId', itemId)

			// if item is in the not started section
			if (sectionNotStartedItems && itemId) {
				let item = Array.from(sectionNotStartedItems.querySelectorAll('div.item'))
					.find(e => e.dataset.id === String(itemId));
				if (item && !read) {
					this.moveItemToNextToRead(event, item)

					item = Array.from(itemIssuesSection.querySelectorAll('div.item'))
						.find(e => e.dataset.id === String(itemId));
					// todo : then update the volume
					this.updateItemList(event, item)
				}
			}
			//  if item is in the read next section
			else if (itemIssuesSection && itemId) {
				const item = Array.from(itemIssuesSection.querySelectorAll('div.item'))
					.find(e => e.dataset.id === String(itemId));
				// todo : then update the volume
				this.updateItemList(event, item)
				// const itemIssue = item.querySelector('li.item-issue');
				// if (itemIssue.length) {
				// }
			}


			return data;

		}).catch(error => {
			console.log(error);
		});
	}

	/** VOLUME PAGE */

	setVolumeIssueRead(event, issueId, was_read) {

		console.log('%csetVolumeIssueRead', 'font-size: 20px');

		const section = document.querySelector('section#volume-issues');
		if (!section) {
			return;
		}

		const issue = section.querySelector(`.issue[data-id="${issueId}"]`);
		issue.dataset.read = was_read ? '0' : '1';
		issue.classList.toggle('read', !was_read);
	}
}