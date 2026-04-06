import { Head, usePage } from '@inertiajs/react';
import { QRCodeSVG } from 'qrcode.react';
import { ScanLine } from 'lucide-react';

export default function PublicQrCode({ qrValue, userName }: { qrValue: string; userName: string }) {
    return (
        <>
            <Head title="My QR Code" />
            <div className="flex flex-col items-center gap-6 p-6 pt-8">

                {/* Heading */}
                <div className="text-center">
                    <div className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
                        <ScanLine size={24} className="text-emerald-600" />
                    </div>
                    <h1 className="text-xl font-bold">Your Recycling QR</h1>
                    <p className="mt-1 text-sm text-muted-foreground">Hold this up to the Mobius bin camera</p>
                </div>

                {/* QR code */}
                <div className="rounded-3xl border-4 border-emerald-600 bg-white p-5 shadow-lg">
                    <QRCodeSVG
                        value={qrValue}
                        size={240}
                        level="H"
                        includeMargin={false}
                    />
                </div>

                <p className="text-center text-sm font-medium text-muted-foreground">{userName}</p>

                {/* Steps */}
                <div className="w-full rounded-2xl border bg-card p-4">
                    <p className="mb-3 text-sm font-semibold">Steps</p>
                    <div className="flex flex-col gap-2">
                        {[
                            'Open the Mobius bin lid',
                            'Hold your phone QR to the camera',
                            'Wait for the green light',
                            'Drop in your cup, lid, or straw',
                        ].map((step, i) => (
                            <div key={i} className="flex items-start gap-3">
                                <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">
                                    {i + 1}
                                </span>
                                <p className="text-sm text-muted-foreground">{step}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </>
    );
}
