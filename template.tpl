{{ set lol = 4 }}

{{ set array = [
	['id' => 1, 'name' => 'lol'],
	['id' => 2, 'name' => 'bonsoir'],
	['id' => 3, 'name' => 'Gtuenmorgen']
] }}

{{ set array.3 = [
	1,
	2,12,
] }}

{{ filter:strtolower }}
	LOLILOL
{{ endfilter }}

{{ doIt("LOL i lol", pouet) }}

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