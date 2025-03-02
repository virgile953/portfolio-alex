import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/react';
import { useMemo } from 'react';
import ProjectForm from './ProjectForm';

interface Project {
    id: number;
    title: string;
    description: string;
    type: string;
    url: string | null;
    images: string | null; // JSON string
    stl_files: string | null; // JSON string
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

interface Props {
    project: Project;
}

export default function Edit({ project }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Projects',
            href: '/projects',
        },
        {
            title: project.title,
            href: route('projects.show', project.id),
        },
        {
            title: 'Edit',
            href: route('projects.edit', project.id),
        },
    ];

    // Parse JSON strings into arrays for the form
    const parsedImages = useMemo(() => {
        if (!project.images) return null;
        try {
            return typeof project.images === 'string' ? JSON.parse(project.images) : project.images;
        } catch (e) {
            console.error('Error parsing images:', e);
            return null;
        }
    }, [project.images]);

    const parsedStlFiles = useMemo(() => {
        if (!project.stl_files) return null;
        try {
            return typeof project.stl_files === 'string' ? JSON.parse(project.stl_files) : project.stl_files;
        } catch (e) {
            console.error('Error parsing stl_files:', e);
            return null;
        }
    }, [project.stl_files]);

    // Parse specifications if it's a JSON string
    const parsedSpecifications = useMemo(() => {
        if (!project.specifications) return {
            dimensions: '',
            weight: '',
            print_time: '',
            print_settings: ''
        };

        if (typeof project.specifications === 'string') {
            try {
                return JSON.parse(project.specifications);
            } catch (e) {
                console.error('Error parsing specifications:', e);
                return {
                    dimensions: '',
                    weight: '',
                    print_time: '',
                    print_settings: ''
                };
            }
        }

        return project.specifications;
    }, [project.specifications]);

    const { data, setData, processing, errors } = useForm<Partial<Project>>({
        title: project.title || '',
        description: project.description || '',
        type: project.type || '',
        url: project.url || '',
        materials: project.materials || '',
        completion_date: project.completion_date || '',
        featured: project.featured || false,
        images: parsedImages, // Use parsed arrays
        stl_files: parsedStlFiles, // Use parsed arrays
        specifications: parsedSpecifications, // Use parsed specifications
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        console.log("Submitting with data:", data);

        // Create an actual FormData object, not just a Record
        const formData = new FormData();

        // Add basic fields
        formData.append('_method', 'PUT');
        formData.append('title', data.title || '');
        formData.append('description', data.description || '');
        formData.append('type', data.type || '');
        formData.append('url', data.url || '');
        formData.append('materials', data.materials || '');
        formData.append('completion_date', data.completion_date || '');
        formData.append('featured', data.featured ? '1' : '0');

        // Handle specifications (convert to JSON string)
        if (data.specifications) {
            formData.append('specifications', JSON.stringify(data.specifications));
        }

        // Handle existing images vs new image uploads
        if (data.images) {
            if (Array.isArray(data.images) && data.images.length > 0) {
                if (typeof data.images[0] === 'string') {
                    // These are existing images (paths)
                    formData.append('existing_images', JSON.stringify(data.images));
                } else {
                    // These are new file uploads
                    for (let i = 0; i < data.images.length; i++) {
                        formData.append(`images[${i}]`, data.images[i]);
                    }
                }
            }
        }

        // Handle existing STL files vs new STL uploads
        if (data.stl_files) {
            if (Array.isArray(data.stl_files) && data.stl_files.length > 0) {
                if (typeof data.stl_files[0] === 'string') {
                    // These are existing STL files (paths)
                    formData.append('existing_stl_files', JSON.stringify(data.stl_files));
                } else {
                    // These are new file uploads
                    for (let i = 0; i < data.stl_files.length; i++) {
                        formData.append(`stl_files[${i}]`, data.stl_files[i]);
                    }
                }
            }
        }

        // Debug what's in the FormData
        for (const pair of formData.entries()) {
            console.log(`${pair[0]}: ${pair[1]}`);
        }

        router.post(route('projects.update', project.id), formData, {
            forceFormData: true,
            preserveScroll: true,
            onError: (errors) => {
                console.error('Form submission errors:', errors);
            },
            onSuccess: () => {
                console.log('Form submitted successfully!');
            }
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${project.title}`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="mb-6 text-3xl font-bold">Edit Project: {project.title}</h1>

                <ProjectForm data={data} setData={setData} errors={errors} processing={processing} handleSubmit={handleSubmit} isEditing={true} />
            </div>
        </AppLayout>
    );
}
