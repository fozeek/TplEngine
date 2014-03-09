{{ set lol = 4 }}

{{ set array = [
	['id' => 1, 'name' => 'lol'],
	['id' => 2, 'name' => 'bonsoir'],
	['id' => 3, 'name' => 'Gtuenmorgen']
] }}

{{ macro uneMacro_de_ouf(test, testou) }}
	{{test}} de {{testou}}
{{ endmacro }}

{{ set array.3 = [
	1,
	2,12,
] }}

{{ filter:strtolower }}
	LOLILOL
{{ endfilter }}


{{ uneMacro_de_ouf("putin", "ouf") }}

{{ list|join(', ') }}

{{ set users = [
	['id' => 1, 'name' => 'lol'],
	['id' => 2, 'name' => 'bonsoir'],
	['id' => 3, 'name' => 'Gtuenmorgen'],
] }}


{{ for name from lol to array.3.2 }}
	{{ name }}
{{ endfor }}

<br /><br />
{{ for xdlol in users }}
	{{ xdlol.id }} : {{ xdlol.name }}<br />
{{ endfor }}

{{ helper:form form.lol mistakene look }}