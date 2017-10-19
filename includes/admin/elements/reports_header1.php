<?php

/**
 * Outputs the reports header
 *
 *
 */
global $wpdb;
 
?>
<div class="elementa-row"> <!--Wraps full report-->
<div class="col s12 l8"> <!--Wraps main area report-->
<div class="filter-toggle-btn">
	<button @click.prevent="toggleFiltersShowing"  class="elementa-btn" v-show="!filtersShowing">Filters</button>
	<button @click.prevent="applyFilters"  class="elementa-btn orange white-text" v-show="filtersShowing">Apply</button>
</div>
<div class="elementa-row hubaga-filters z-depth-1" v-show="filtersShowing">
	<div class="col s12 m4"  v-hubaga-click-outside="hideDateFilter" >
	
		<button 
			@click.prevent="toggleDateFilter" 
			class="hubaga-filter-btn elementa-btn elementa-btn-block waves-effect waves-light white black-text">Date<i
			class="dashicons btn-right"
			:class="{ 'dashicons-arrow-down': !filters.dateFilter.showing, 'dashicons-arrow-up': filters.dateFilter.showing, }"
			></i>
		</button>
			
		<ul class="list-group elementa-dropdown-content" v-if="filters.dateFilter.showing">
			<li v-for="dateFilter, category in filters.dateFilter.options" class="list-group-item"  :class="{ 'deep-orange white-text': isDateFilterCategoryShowing(category) }">
				
				<a 
					href="#" 
					@click.prevent="filters.dateFilter.currentKey.category = category" 
					class="hubaga-block hubaga-unstyled black-text" 
					:class="{ 'white-text': isDateFilterCategoryShowing(category) }"
				>{{category}}<i
				class="dashicons btn-right" 
				:class="{ 'dashicons-arrow-down': isDateFilterCategoryShowing(category), 'dashicons-arrow-right': !isDateFilterCategoryShowing(category), }"
				></i></a>
				
				<ul class="list-group-inner" v-if="isDateFilterCategoryShowing(category)">
					<li v-for="dfilter in dateFilter" class="list-group-inner-item">
						<input name="hubaga-date-filter" @change="changeDateFilter(dfilter)" type="radio" :id="dfilter.label"> <label :for="dfilter.label"> {{dfilter.label}} </label>
					</li>
				</ul>
			</li>
		</ul>
		<p class="hubaga-muted">{{filters.dateFilter.label}}</p>
	</div>
	
	<div class="col s12 m4">
		<hubaga-reports-selectize v-model="filters.platformFilter" :options="filters.platforms"></hubaga-reports-selectize>
	</div>
	
	<div class="col s12 m4">
		<hubaga-reports-selectize v-model="filters.browserFilter" :options="filters.browsers"></hubaga-reports-selectize>
	</div>
</div>