import Controller from './abstract_controller';

/**
 * @todo desktop search
 * @todo add loader to volumes images
 * @todo add added volume in "readNextNotStarted" section
 * @todo add loader on badge after clicking on "+" button
 * @todo add loader when searching for volumes
 */
export default class extends Controller {

	static targets = ['volume']

	connect() {
		// this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/hello_controller.js';
	}

	openVolume(id) {
		window.location.href = `/volume/${id}`;
	}

	setVolumeAsAdded(idc, id) {
		if (!id) {
			return false;
		}

		const volume = this.volumeTargets.find(e => e.dataset.idc === idc);
		volume.dataset.added = "1";
		volume.dataset.id = id;

		const badge = volume.querySelector('.result-badges svg');
		badge.dataset.id = id;
		badge.dataset.added = "1";
		badge.classList.add('fa-circle-check');
		badge.classList.remove('fa-plus-circle');
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
			added = element.dataset.added;

		if (!idc) {
			return false;
		}

		if (added) {
			// return this.see(event);
			return true;
		}

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