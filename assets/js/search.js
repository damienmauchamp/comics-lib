window.onload = () => {

	let scrollEvent = null,
		scrollData = {};

// let simulated = false;
	let test = false;

// const element = document;
	const element = document.querySelector('main'),
		searchBar = {
			search: document.querySelector('#search'),
			bar: document.querySelector('#search-bar'),
			container: document.querySelector('#search-bar-container'),
			input: document.querySelector('#search-bar input'),
			cancelButton: document.querySelector('#search-bar button.cancel'),
			results: document.querySelector('#search-results ul.results'),
			isOpen: () => {
				return searchBar.bar.classList.contains('open');
			},
			isFocus: () => {
				return searchBar.search.classList.contains('focus');
			}
		},
		barData = {
			// maxHeight: searchBar.container.offsetHeight + searchBar.input.offsetHeight,
			maxHeight: null,
		};

	console.log('loaded')
	barData.maxHeight = searchBar.container.offsetHeight + searchBar.input.offsetHeight;

	element.addEventListener("touchstart", function (e) {
		scrollEvent = e;
		scrollData = {
			scrollTop: element.scrollTop,
			barHeight: searchBar.bar.offsetHeight,
			containerHeight: searchBar.container.offsetHeight,
			inputHeight: searchBar.input.offsetHeight,
			direction: null,
			target: e.target,
			continue: !$(e.target).closest('#search').length,
		};
		// console.log('START', 'barData.maxHeight:', barData.maxHeight)
		console.log('START', 'e.target:', e.target);
	});
	element.addEventListener("touchmove", function (e) {

		if (!scrollEvent) {
			return true;
		}
		if (!scrollData.continue) {
			return false;
		}

		let delta = e.touches[0].pageY - scrollEvent.touches[0].pageY,
			direction = delta > 0 ? 'up' : 'down'; // direction of scroll, not the direction of the finger

		let currentScrollTop = element.scrollTop;
		// let barTotalHeight = scrollData.containerHeight + scrollData.inputHeight;

		console.log('currentScrollTop:', currentScrollTop);
		console.log('scrollData:', scrollData);
		console.log('delta:', delta, direction);

		scrollData.direction = direction;

		// DOWN
		if (direction === 'down') {

			// test = true;

			let newHeight = barData.maxHeight - currentScrollTop;

			if (currentScrollTop === 0 && !searchBar.isOpen()) {
				newHeight = 0;
			}

			console.log('currentScrollTop:', currentScrollTop);
			console.log('searchBar.isOpen():', searchBar.isOpen());
			console.log('newHeight:', newHeight);
			if (newHeight < 0) {
				newHeight = 0;
				// searchBar.bar.style.height = `${barData.maxHeight - currentScrollTop}px`;
			} else if (newHeight > barData.maxHeight) {
				newHeight = barData.maxHeight;
			}
			searchBar.bar.style.height = `${newHeight}px`;

			// todo : si on est tout en haut, scrollTop à 0
			if (currentScrollTop < barData.maxHeight) {
				// element.scrollTop = 0;
			}
			console.log('newHeight2:', newHeight);

			// todo
			// return true;
		} else if (direction === 'up') {

			// if (searchBar.bar.style.height)

			// UP

			// console.log('barTotalHeight:', barTotalHeight);

			if (delta > 0 && delta < barData.maxHeight) {
				// searchBar.bar.style.height = `${barTotalHeight - delta}px`;
				searchBar.bar.style.height = `${delta}px`;
				console.log('NOUVELLE HAUTEUR:', delta);
			}

			// console.log('Move delta:', direction, delta)
			// console.log('e:', e, e.touches)
			// console.log('scrollEvent:', scrollEvent, scrollEvent.touches)
			// console.log('scrollData:', scrollData)
			// console.log('element.scrollTop:', element.scrollTop, 'scrollEvent.target.scrollTop:', scrollEvent.target.scrollTop)
		}
		console.log('-----------------------------------------------------')
	});
	element.addEventListener("touchend", function (e) {

		if (!scrollEvent) {
			return true;
		}
		if (!scrollData.continue) {
			return false;
		}

		console.log('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!')
		console.log('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!')
		console.log('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!')
		console.log('END', 'barData.maxHeight:', barData.maxHeight)

		// getting the height of the bar
		let barHeight = searchBar.bar.offsetHeight;

		let isOpened = searchBar.bar.classList.contains('open');

		console.log('touchend', 'barHeight:', barHeight, 'barData.maxHeight:', barData.maxHeight);
		console.log('searchBar.bar.style.height', searchBar.bar.style.height)

		// if (simulated) {
		// 	simulated = false;
		// 	return true;
		// }
		let currentScrollTop = element.scrollTop;

		// let divider = scrollData.direction === 'down' ? 2 : 3;
		// down : closing
		// up : opening
		let aaaa = scrollData.direction === 'down' ?
			(barData.maxHeight * 2 / 3) : (barData.maxHeight / 2);
		console.log('barHeight', barHeight)
		console.log('scrollData.direction', scrollData.direction)
		console.log('barData.maxHeight', barData.maxHeight)
		console.log('aaaa', aaaa)

		if (barHeight > aaaa) {
			console.log('OPEN');
			searchBar.bar.classList.toggle('open', true);
			// if (!test) {

			if (currentScrollTop < barData.maxHeight) {
				// element.scrollTop = 0;
				$(element).stop().animate({scrollTop: 0});
			} else {
				console.log('nope bloquée ?', {
					currentScrollTop: currentScrollTop,
					barData_maxHeight: barData.maxHeight,
					"searchBar.bar.style.height": searchBar.bar.style.height,
				})
			}
			// searchBar.bar.style.height = `${barData.maxHeight}px`;
			$(searchBar.bar).animate({
				height: barData.maxHeight,
			});

			// }
			// element.scrollTop = 0;
			// simulated = true;
		} else {
			console.log('CLOSE');
			searchBar.bar.classList.toggle('open', false);

			console.log('currentScrollTop', currentScrollTop);
			console.log('element.scrollTop', element.scrollTop);
			console.log('searchBar.bar.style.height', searchBar.bar.style.height);

			// todo : animate
			// searchBar.bar.style.height = 0;
			$(searchBar.bar).animate({
				height: 0,
			});

			unFocusBar();

			if (isOpened && currentScrollTop < barData.maxHeight) {
				// element.scrollTop = 0;
				$(element).stop().animate({scrollTop: 0});
			} else if (isOpened) {
				console.log('barData.maxHeight', barData.maxHeight);
				console.log('currentScrollTop', currentScrollTop);
			}
		}

		console.log('currentScrollTop:', currentScrollTop, '->', element.scrollTop, scrollData.direction);
		scrollEvent = null;
		scrollData = {};

	});

	const unFocusBar = () => {

		// removing the class
		searchBar.search.classList.toggle('focus', false);

		document.body.classList.toggle('searching', false);

	};

	// focusing the input
	searchBar.input.addEventListener('focus', function (e) {

		// adding the class
		searchBar.search.classList.toggle('focus', true);

		document.body.classList.toggle('searching', false);

		console.log('focus');
	});

	// focusing the input
	// tmp
	function delay(fn, ms) {
		let timer = 0
		return function (...args) {
			clearTimeout(timer)
			timer = setTimeout(fn.bind(this, ...args), ms || 0)
		}
	}

	// function debounce(func, wait, immediate) {
	// 	var timeout;
	// 	return function () {
	// 		var context = this, args = arguments;
	// 		var later = function () {
	// 			timeout = null;
	// 			if (!immediate) func.apply(context, args);
	// 		};
	// 		var callNow = immediate && !timeout;
	// 		clearTimeout(timeout);
	// 		timeout = setTimeout(later, wait);
	// 		if (callNow) func.apply(context, args);
	// 	};
	// };

	const search = (term) => {

		if (!term.length) {
			searchBar.results.innerHTML = '';
			return false;
		}

		// fetching the results
		console.log('Search:', term);
		fetch(`search/api/volume?q=${term}`)
			.then(response => response.json())
			.then(data => {
				console.log('data', data);
				searchBar.results.innerHTML = '';
				data.forEach(item => {
					searchBar.results.innerHTML += item.html;
				});
			})
			.catch(error => {
				console.log('error', error);
			});
	}

	// searchBar.input.addEventListener('keyup', function (e) {
	//
	// 	console.log('type:', e.target.value);
	// 	debounce(search, 500)(e.target.value);
	//
	// 	// delay((e) => search(e.target.value), 500)(e);
	//
	//
	// 	// debounce(function () {
	//
	// 	// searchBar.results.innerHTML = '';
	// });

	searchBar.input.addEventListener('keyup', delay(function (e) {
		search(e.target.value);
	}, 500));

	// bluring the input
	// searchBar.input.addEventListener('blur', function (e) {
	//
	// 	// todo check blur target (replace it ?)
	// 	// console.log('blur', e, e.target)
	// 	console.log('blur', e)
	// 	unFocusBar();
	// });

	// clicking on the "Cancel" button
	searchBar.cancelButton.addEventListener('click', function (e) {
		unFocusBar();
	});

	// searching
}

// searchBar
// bar