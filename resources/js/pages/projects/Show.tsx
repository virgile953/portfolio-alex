import StlViewer from '@/components/stl-viewer';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { useEffect, useMemo } from 'react';
import { getFileUrl } from '@/utils/storage';

interface Project {
    id: number;
    title: string;
    description: string;
    type: string;
    url: string | null;
    images: string | null; // This is actually a JSON string, not an array
    stl_files: string | null; // This is actually a JSON string, not an array
    materials: string | null;
    specifications:
        | string
        | {
              dimensions?: string;
              weight?: string;
              print_time?: string;
              print_settings?: string;
              [key: string]: any;
          }
        | null;
    completion_date: string | null;
    featured: boolean;
}


interface Props {
    project: Project;
}

export default function Show({ project }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Projects',
            href: '/projects',
        },
        {
            title: project.title,
            href: route('projects.show', project.id),
        },
    ];

    // Parse JSON strings into arrays
    const images = useMemo(() => {
        if (!project.images) return [];
        try {
            return typeof project.images === 'string' ? JSON.parse(project.images) : project.images;
        } catch (e) {
            console.error('Error parsing images:', e);
            return [];
        }
    }, [project.images]);

    const stlFiles = useMemo(() => {
        if (!project.stl_files) return [];
        try {
            return typeof project.stl_files === 'string' ? JSON.parse(project.stl_files) : project.stl_files;
        } catch (e) {
            console.error('Error parsing stl_files:', e);
            return [];
        }
    }, [project.stl_files]);

    // Parse specifications if it's a JSON string
    const parsedSpecifications = useMemo(() => {
        if (!project.specifications) return null;

        if (typeof project.specifications === 'string') {
            try {
                return JSON.parse(project.specifications);
            } catch (e) {
                console.error('Error parsing specifications:', e);
                return null;
            }
        }

        return project.specifications;
    }, [project.specifications]);

    // Format specifications for display - filter out empty values
    const formattedSpecifications = useMemo(() => {
        if (!parsedSpecifications) return [];

        return Object.entries(parsedSpecifications)
            .filter(([_, value]) => value && String(value).trim() !== '')
            .map(([key, value]) => ({
                label: key
                    .replace(/_/g, ' ')
                    .split(' ')
                    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
                    .join(' '),
                value: String(value),
            }));
    }, [parsedSpecifications]);

    useEffect(() => {
        console.log('Project:', project);
        console.log('Parsed Images:', images);
        console.log('Parsed STL Files:', stlFiles);
        console.log('Parsed Specifications:', parsedSpecifications);
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={project.title} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-3xl font-bold">{project.title}</h1>
                    <div className="flex gap-2">
                        <Link href={route('projects.edit', project.id)} className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                            Edit
                        </Link>
                        <Link href={route('projects.index')} className="rounded bg-gray-300 px-4 py-2 hover:bg-gray-400">
                            Back
                        </Link>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-8 md:grid-cols-2">
                    {/* 3D Model Viewer */}
                    <div className="h-[500px] overflow-hidden rounded-lg bg-gray-100 p-0">
                        {stlFiles.length > 0 ? (
                            <StlViewer
                                filepath={getFileUrl(stlFiles[0])}
                                width="100%"
                                height="100%"
                            />
                        ) : (
                            <div className="flex h-full items-center justify-center rounded-lg bg-gray-200">
                                <p className="text-gray-500">No 3D model available</p>
                            </div>
                        )}
                    </div>

                    {/* Project Details */}
                    <div>
                        <div className="mb-4">
                            <div className="flex items-center">
                                <h2 className="text-2xl font-semibold">{project.title}</h2>
                                {project.featured && <span className="ml-2 rounded bg-yellow-100 px-2 py-1 text-xs text-yellow-800">Featured</span>}
                            </div>
                            <p className="mt-1 text-gray-600">{project.type}</p>
                        </div>

                        <div className="prose mb-6 max-w-none">
                            <p>{project.description}</p>
                        </div>

                        <div className="mb-6 grid grid-cols-1 gap-4">
                            {project.materials && (
                                <div>
                                    <h3 className="text-lg font-semibold">Materials</h3>
                                    <p>{project.materials}</p>
                                </div>
                            )}

                            {formattedSpecifications.length > 0 && (
                                <div>
                                    <h3 className="text-lg font-semibold">Specifications</h3>
                                    <div className="mt-2 grid grid-cols-1 gap-2">
                                        {formattedSpecifications.map(({ label, value }) => (
                                            <div key={label} className="flex flex-col border-b pb-2 last:border-0">
                                                <span className="text-sm text-gray-500">{label}</span>
                                                <span className="font-medium">{value}</span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {project.completion_date && (
                                <div>
                                    <h3 className="text-lg font-semibold">Completed</h3>
                                    <p>{new Date(project.completion_date).toLocaleDateString()}</p>
                                </div>
                            )}

                            {project.url && (
                                <div>
                                    <h3 className="text-lg font-semibold">Project URL</h3>
                                    <a href={project.url} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">
                                        {project.url}
                                    </a>
                                </div>
                            )}
                        </div>

                        {/* Project Images */}
                        {images.length > 0 && (
                            <div>
                                <h3 className="mb-2 text-lg font-semibold">Images</h3>
                                <div className="grid grid-cols-2 gap-2">
                                    {images.map((image: string, index: number) => (
                                        <img
                                            key={index}
                                            src={getFileUrl(image)}
                                            alt={`${project.title} - Image ${index + 1}`}
                                            className="h-auto w-full rounded-lg"
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
