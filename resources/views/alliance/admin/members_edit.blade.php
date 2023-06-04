<form action="game.php?page=alliance&mode=admin&edit=members&id={{ $user_id }}" name="edit_user_rank" method="POST" role="form">
    <select name="newrang">
        @foreach ($options as $item)
        <option onclick="document.edit_user_rank.submit();" value="{{ $item['id'] }}"{{ $item['selected'] }}>
            {{ $item['rank'] }}
        </option>
        @endforeach
    </select>
    <input type="submit" value="{{ __('game/alliance.al_ok') }}" />
</form>