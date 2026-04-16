<!DOCTYPE html>
<html>
<body>
    <h1>{{ $location->name }}</h1>
    @foreach($guardians as $g)
        <div>{{ $g->name }}</div>
    @endforeach
</body>
</html>
