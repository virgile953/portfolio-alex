import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import ProjectForm from './ProjectForm';

interface Project {
    id: number;
    title: string;
    description: string;
    type: string;
    url: string | null;
    images: string[] | File[] | null;
    stl_files: string[] | File[] | null;
    materials: string | null;
    specifications: {
        dimensions?: string;
        weight?: string;
        print_time?: string;
        print_settings?: string;
        [key: string]: any;
    } | null;
    completion_date: string | null;
    featured: boolean;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: '/projects',
    },
    {
        title: 'Create Project',
        href: '/projects/create',
    }
];

export default function Create() {
    const { data, setData, post, processing, errors } = useForm<Partial<Project>>({
        title: '',
        description: '',
        type: '',
        url: '',
        materials: '',
        completion_date: '',
        featured: false,
        images: null,
        stl_files: null,
        specifications: {
            dimensions: '',
            weight: '',
            print_time: '',
            print_settings: ''
        },
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        console.log("Submitting new project with data:", data);

        post(route('projects.store'), {
            onSuccess: () => {
                console.log('Project created successfully!');
            },
            onError: (errors) => {
                console.error('Form submission errors:', errors);
            },
            preserveScroll: true
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Project" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-3xl font-bold mb-6">Create Project</h1>

                <ProjectForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    handleSubmit={handleSubmit}
                    isEditing={false}
                />
            </div>
        </AppLayout>
    );
}
