<?php

/**
 * Outputs the reports header
 *
 *
 */
?>
<div class="hubaga-reports-cards">

	<nav class="nav-tab-wrapper" style="margin-bottom: 2rem;">
		<a	v-for="card in cards"
			@click.prevent="changeCurrentCard(card)"
			href="#"
			class="nav-tab"
			:class="{ 'nav-tab-active': isCurrentCard(card) }"
		>{{card.title}}</a>
	</nav>

	<div v-for="card in cards" class="report-card-wrapper" v-show="isCurrentCard(card)">
	<div class="z-depth-1" style="padding: 1em;">
		<h2>{{card.title}}</h2>
		<ul class="list-group-inner hubaga-unstyled elementa-row">
			<li v-for="value, aggregate in card.aggregates" class="list-group-inner-item col s12 m6 l4">
				<div class="z-depth-1 hubaga-report-card-aggregate cyan white-text"><span class="card-num" v-html="value"></span>{{aggregate}} </div>
			</li>
		</ul>

		<hubaga-flot-chart :datasets="card.datasets" :options="card.options" :current-card="currentCard"></hubaga-flot-chart>
	</div>
	</div>
</div>
