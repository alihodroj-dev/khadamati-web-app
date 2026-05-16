<div class="w-full rounded-xl overflow-hidden"
     style="border: 0.5px solid var(--color-border-tertiary, #e5e7eb); background: white;">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse table-auto">
            <thead style="background: #f9fafb; border-bottom: 0.5px solid #e5e7eb;">
                <tr>
                    {{ $head }}
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                {{ $body }}
            </tbody>
        </table>
    </div>
</div>