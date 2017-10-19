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
	<div class="col s12 m4">
		<hubaga-reports-selectize :value="filters.dateFilter" v-model="filters.dateFilter" :options="filters.dates"></hubaga-reports-selectize>
	</div>

	<div class="col s12 m4">
		<hubaga-reports-selectize :value="filters.platformFilter" v-model="filters.platformFilter" :options="filters.platforms"></hubaga-reports-selectize>
	</div>

	<div class="col s12 m4">
		<hubaga-reports-selectize :value="filters.browserFilter" v-model="filters.browserFilter" :options="filters.browsers"></hubaga-reports-selectize>
	</div>
</div>
