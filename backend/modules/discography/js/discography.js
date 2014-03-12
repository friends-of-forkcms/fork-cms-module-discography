/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Interaction for the discography module
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
jsBackend.discography =
{
	// constructor
	init: function()
	{
		jsBackend.discography.tracks.init();

		// do meta
		if($('#title').length > 0) $('#title').doMeta();
	}
};

jsBackend.discography.tracks =
{
	// holds a dummy row, we will use this to base all records on.
	dummyTrack: null,

	// wrapper element that holds all the rows
	tracksWrapper: null,

	/**
	 * Constructor-alike
	 */
	init: function()
	{
		// track handling
		jsBackend.discography.tracks.tracksWrapper = $('#tracksWrapper');
		if(jsBackend.discography.tracks.tracksWrapper.length > 0)
		{
			jsBackend.discography.tracks.setDummyTrack($('tr#dummyTrack'));
			jsBackend.discography.tracks.buildTable();
			jsBackend.discography.tracks.save();

			jsBackend.discography.tracks.bindDragAndDrop();
			jsBackend.discography.tracks.bindAdd();
			jsBackend.discography.tracks.bindDelete();

			jsBackend.discography.tracks.showHideNoItems();
		}
	},

	/**
	 * Load the dummy element and remove it from the DOM (so we dont hook stuff on it)
	 */
	setDummyTrack: function(element)
	{
		// reset some stuff
		element.attr('id', '');

		// cache for building later
		jsBackend.discography.tracks.dummyTrack = element;

		// remove from DOM (so it cant be used for sorting or rebuilding)
		element.remove();
	},

	/**
	 * Build the initial table with tracks.
	 */
	buildTable: function()
	{
		$(tracks).each(function()
		{
			jsBackend.discography.tracks.addTrack(this);
		});
	},

	/**
	 * Save all tracks to a hidden field so we can process it in PHP.
	 */
	save: function()
	{
		// remove all tracks before reading them
		$('input.tracks').remove();

		// generate track numbers
		jsBackend.discography.tracks.generateTrackNumbers();

		// Add hidden tracks to parse in PHP
		jsBackend.discography.tracks.tracksWrapper.find('tr.track').each(function()
		{
			$fields = $('input', this).toArray();

			// create a element based on this hidden field
			$track = $('input#dummyTracks').clone();
			$track.attr('name', 'tracks[]');
			$track.attr('id', '');
			$track.addClass('tracks');
			var trackId = $(this).data('id') !== undefined ? $(this).data('id') : '';
			$track.val(trackId + ':::::' + $fields[0].value + ':::::' + ($fields[1].value != '' ? $fields[1].value : '00:00'));

			jsBackend.discography.tracks.tracksWrapper.append($track);
		});

		jsBackend.discography.tracks.showHideNoItems();
	},

	/**
	 * Calculate track numbers corresponding to the row index
	 */
	generateTrackNumbers: function()
	{
		jsBackend.discography.tracks.tracksWrapper.find('tr.track').each(function()
		{
			$(this).find('td.tracknr').html($(this).index());
		});
	},

	/**
	 * Add a new track
	 *
	 * @param track
	 */
	addTrack: function(track)
	{
		// clone based on dummy but add personal data
		$element = jsBackend.discography.tracks.dummyTrack.clone();
		$element.find('input#track').val(track.title);
		$element.find('input#duration').val(track.duration);
		$element.data("id", track.id);
		$element.find('input').on('blur', jsBackend.discography.tracks.save);

		// add to wrapper
		jsBackend.discography.tracks.tracksWrapper.find('tbody').append($element);

		// generate track numbers
		jsBackend.discography.tracks.generateTrackNumbers();

		jsBackend.discography.tracks.showHideNoItems();
	},

	/**
	 * Show "no items" message based on the amount of items in the table.
	 */
	showHideNoItems: function()
	{
		//console.log('hideshow');
		if(jsBackend.discography.tracks.tracksWrapper.find('tr.track').length > 0)
		{
			$('tr.noItemsHolder').hide();
		}
		else
		{
			$('tr.noItemsHolder').show();
		}
	},

	/**
	 * Allow reordering of the tracks
	 */
	bindDragAndDrop: function()
	{
		// destroy default drag and drop
		$('.sequenceByDragAndDrop tbody').sortable('destroy');

		// set sortable by drag and drop
		$('.sequenceByDragAndDrop tbody').sortable({
			items: 'tr',
			handle: 'td.dragAndDropHandle',
			placeholder: 'dragAndDropPlaceholder',
			stop: jsBackend.discography.tracks.save
		});
	},

	/**
	 * Bind the add button.
	 */
	bindAdd: function()
	{
		$('a.addTrack').on('click', function(e)
		{
			e.preventDefault();
			jsBackend.discography.tracks.addTrack('', '');
		});
	},

	/**
	 * Delete a track.
	 */
	bindDelete: function()
	{
		$(document).on('click', 'a.deleteTrack', function(e)
		{
			e.preventDefault();

			$(this).closest('tr').remove();

			jsBackend.discography.tracks.save();
		});
	}
};

$(jsBackend.discography.init);
