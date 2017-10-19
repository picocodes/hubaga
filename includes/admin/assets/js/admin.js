window.hubaga = {};

( function( $ ) {

	//Simple charting component
	Vue.component('hubaga-flot-chart', {
      props: ['datasets', 'options', 'currentCard'],
      template: '<div class="hubaga-flotchart-placeholder"></div>',
      mounted: function () {
		$.plot( $( this.$el ) , this.datasets, this.options);
      },

      watch: {
        datasets: function (datasets) {
			$.plot( $( this.$el ) , datasets, this.options);
        },
        options: function (options) {
			$.plot( $( this.$el ) , this.datasets, options);
        },
		currentCard: function (currentCard) {
			$.plot( $( this.$el ) , this.datasets, this.options);
        }
      }
    });

	//Selectize component
	Vue.component('hubaga-reports-selectize', {
      props: ['options', 'value'],
      template: '<select><slot></slot></select>',
      mounted: function () {
        var vm = this

        $(this.$el)
          .val(this.value)
          // init selectize
          .selectize({ 'options': this.options })
          // emit event on change.
          .on('change', function () {
            vm.$emit('input', this.value)
          })

      },
      watch: {
        value: function (value) {
          // update value
          $(this.$el).val(value).trigger('change')
        },
        options: function (options) {
          // update options
          $(this.$el).selectize({ 'options': this.options })
        }
      },
      destroyed: function () {
        $(this.$el).off().selectize('destroy')
      }
    })

	//Custom click outside directive with caching
	Vue.directive( 'hubaga-click-outside', {
		bind: function( el, binding, vNode ){
			function handler(e){
				if(!vNode.context)return

				if(!$(el).is(e.target) && $(el).has(e.target).length === 0 ) {
					el.__hubagaClickOutside__.callback(e)
				}
			}

			el.__hubagaClickOutside__ = {
				handler: handler,
				callback: binding.value
			}

			$(document).on('mouseup', handler );

		},

		update: function( el, binding ){
			el.__hubagaClickOutside__.callback = binding.value
		},

		unbind: function( el, binding ){
			$(document).off('mouseup', el.__hubagaClickOutside__.handler )
			delete el.__hubagaClickOutside__
		}
	});

	//Reports app
	window.hubaga.reportsVue = function( data, el ){
		return new Vue({
			el: el,

			data: data,

			methods: {

				isFiltersShowing: function(){
					return this.filters.showing === false
				},

				toggleFiltersShowing: function(){
					this.filtersShowing = !this.filtersShowing
				},

				applyFilters: function(){

					var vm = this;
					vm.loading	 = true;
					$.get(
						this.ajaxUrl,
						this.filters,
						function( data, status ) {
							vm.loading	 = false;
							vm.cards	 = data.cards;
							vm.filtersShowing = false;
					}).fail(function(){
						vm.loading	 = false;
						vm.filtersShowing = false;
					});
				},

				hideStream: function( stream ) {
					this.currentStream = false
				},

				showStream: function(key){
					this.currentStream = key
				},

				isCurrentCard: function( card ){
					return this.currentCard == card.title
				},

				changeCurrentCard: function( card ){
					this.currentCard = card.title
				},

			},

		});
	}

} )( jQuery );
