<html>
    {{-- @php
        if (auth()->user()->role !== 'user') {
        abort(403, 'Unauthorized');
        }
    @endphp     --}}
     
    <form action="">
        <h1>Add Property</h1>
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required><br><br>
        
        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea><br><br>
        
        <label for="price">Price:</label>
        <input type="number" id="price" name="price" required><br><br>
        
        <label for="location">Location:</label>
        <input type="text" id="location" name="location" required><br><br>
        
        <label for="type">Type:</label>
        <select id="type" name="type" required>
            <option value="apartment">Apartment</option>
            <option value="house">House</option>
            <option value="condo">Condo</option>
        </select><br><br>
        
        <button type="submit">Add Property</button>
    </form>
</html>