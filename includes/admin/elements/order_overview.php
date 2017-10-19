<?php

/**
 * Outputs the order overview
 *
 *
 */
 
global $post;
$order= hubaga_get_order( $post->ID );

$data = apply_filters( 'hubaga_order_notes_editor_data', array(
	'notes'					=> hubaga_get_order_notes( $order ),
	'newNoteAuthor'			=> hubaga_get_customer_name( get_current_user_id() ),
	'newNoteContent'		=> '',
));
 ?>
<div id="hubaga-note-editor" >
	<h1>Notes</h1>
	<div class='divider'></div>

	<div class="elementa-row">
		<div class="col s8 m10">
			<textarea class="form-control" rows="1" v-model="newNoteContent"></textarea>
		</div>
		<div class="col s4 m2">
			<button href="#" class="elementa-btn" @click.prevent="addNote">Add</button>
		</div>
	</div>

	<ul class="hubaga-order-notes">
		<li v-for="note, key in notes" class="hubaga-note elementa-row">
			<div class="note-content">
				<p>{{note.content}}</p>
			</div>
			<p class="meta">
				{{note.author}} - {{note.date}} <a href="#" class="red-text" role="button" @click.prevent="removeNote(note)">Delete note</a>
			</p>
			
			<input :name="noteName('author',  key)" type="hidden" v-model="note.author">
			<input :name="noteName('content',  key)" type="hidden" v-model="note.content">
			<input :name="noteName('date',  key)" type="hidden" v-model="note.date">
		</li>
	</ul>
	
</div>
<script>

( function( data ) {
	
	//Order Editor
	var orderEditor = new Vue({
		el: '#hubaga-note-editor',
		
		data: data,

		methods: {
			//Notes
			addNote: function () {
				var content = this.newNoteContent && this.newNoteContent.trim()
				
				if (!content) {
					return
				}
				
				this.notes.push({
					author: this.newNoteAuthor,
					content: content,
					date: new Date().toGMTString()
				});
				this.newNoteContent = '';
			},

			removeNote: function (note) {
				this.notes.splice(this.notes.indexOf(note), 1)
			},
			
			noteName: function (prop, key) {
				return 'notes[' + key + '][' + prop + ']';
			},

		},
		
	});
	
	<?php
	/**
	 * Fires when generating the order notes editor js
	 * @since 1.0.0
	 */
	do_action( 'hubaga_order_notes_editor', $order );
	?>
} )( <?php echo wp_json_encode( $data ) ?> );
</script>