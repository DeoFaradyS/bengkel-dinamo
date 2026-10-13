<div>
    @if ($submitted)
        <p><strong>Booking terkirim.</strong> Kami akan menghubungi untuk konfirmasi.</p>
    @else
        <form wire:submit="submit">
            <div style="display:none" aria-hidden="true">
                <input type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <label>Nama
                <input type="text" wire:model="customer_name">
            </label>
            @error('customer_name') <small>{{ $message }}</small> @enderror

            <label>No. HP
                <input type="tel" wire:model="phone" placeholder="081234567890">
            </label>
            @error('phone') <small>{{ $message }}</small> @enderror

            <label>Keluhan
                <textarea wire:model="complaint" rows="3"></textarea>
            </label>
            @error('complaint') <small>{{ $message }}</small> @enderror

            <label>Jadwal
                <input type="datetime-local" wire:model="scheduled_at">
            </label>
            @error('scheduled_at') <small>{{ $message }}</small> @enderror

            @error('throttle') <small>{{ $message }}</small> @enderror

            <button type="submit">Kirim booking</button>
        </form>
    @endif
</div>