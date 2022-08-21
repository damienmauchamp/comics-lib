import Controller from './abstract_controller';

/**
 * @todo desktop search
 * @todo add loader to volumes images
 * @todo add added volume in "readNextNotStarted" section
 * @todo add loader on badge after clicking on "+" button
 * @todo add loader when searching for volumes
 */
export default class extends Controller {

	static targets = ['volume', 'collectionFilter']

	connect() {
		// this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/hello_controller.js';
	}

	openVolume(id) {
		window.location.href = `/volume/${id}`;
	}

	replaceVolumeBadge(volume, type) {
		const badge = volume.querySelector('.result-badges svg');

		console.log('badge', badge)
		console.log('volume', volume)

		const ADDED = 'fa-circle-check',
			LOADING = 'fa-circle-notch',
			ERROR = 'fa-times-circle';

		let className = '',
			spin = false;
		switch (type) {
			case 'added':
				className = ADDED;
				break;
			case 'loading':
				className = LOADING;
				spin = true;
				break;
			case 'error':
				className = ERROR;
				break;
			default:
				return false;
		}

		// removing old signs
		badge.classList.remove(ADDED);
		badge.classList.remove(LOADING);
		badge.classList.remove(ERROR);
		badge.classList.toggle('fa-spin', spin);
		// adding class name
		badge.classList.add(className);
	}

	setVolumeAsAdded(idc, id) {
		if (!id) {
			return false;
		}

		const volume = this.volumeTargets.find(e => e.dataset.idc === idc);
		volume.dataset.added = "1";
		volume.dataset.id = id;

		//
		volume.classList.remove('loading');
		this.replaceVolumeBadge(volume, 'added');
	}

	volume(event) {
		const element = event.target;
		const type = element.dataset.type,
			id = element.dataset.id,
			idc = element.dataset.idc,
			added = !!parseInt(element.dataset.added);

		// const volume = this.volumeTargets.find(e => e.dataset.idc === idc);

		if (!added) {
			return false;
		}
		return this.openVolume(id);
	}

	add(event) {
		const element = event.target;
		const type = element.dataset.type,
			idc = element.dataset.idc,
			added = element.dataset.added,
			volume = this.volumeTargets.find(e => e.dataset.idc === idc);

		if (!idc) {
			return false;
		}

		if (added) {
			// return this.see(event);
			return true;
		}

		//
		volume.classList.add('loading');
		this.replaceVolumeBadge(volume, 'loading');

		// adding
		this.post(`/volume/${idc}/add`, {render: 'home'})
			// .then(response => response.json())
			.then(data => {
				console.log('data', data)
				this.setVolumeAsAdded(idc, data.volume.id);
			})
			.catch(error => {
				console.log('error', error)
			});
	}

	see(event) {
		const element = event.target;
		const type = element.dataset.type,
			id = element.dataset.id;
		//
		// console.log('SEE', type, id, element)
		//
		// this.openVolume(id);
	}
}