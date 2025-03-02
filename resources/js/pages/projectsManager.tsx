import StlViewer from '@/components/stl-viewer';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
{
title: 'Ajouter un projet',
href: '/new-project',
},
];

export default function projectsManager() {
return (
<AppLayout breadcrumbs={breadcrumbs}>

    <Head title="Nouveau projet" />
    <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="text-5xl mb-6">coucou</div>

        <div className="flex flew-col md:flex-row gap-4 overflow-x-auto pb-4">
            <StlViewer filepath="/models/benchy.stl" name="Benchy!" description="This is a benchy" width={400}
                height={400} />

            <StlViewer filepath="/models/Deer.stl" name="Deer"
                description="This is a sample 3D model rendered with Three.js" width={400} height={400} />
            {/*
            <StlViewer filepath="/models/Diffuseur.stl" name="Beaucoup polygones"
                description="This is a sample 3D model rendered with Three.js" width={400} height={400} /> */}
            <StlViewer filepath="/models/benchy.stl" name="Benchy!" description="This is a benchy" width={400}
                height={400} />
        </div>
    </div>
</AppLayout>
);
}
