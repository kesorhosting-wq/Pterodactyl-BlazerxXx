import Code from '@/components/elements/Code';
import Label from '@/components/elements/Label';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import React, { useEffect } from 'react';
import { Allocation } from '@/api/server/getServer';
import http, { httpErrorToHuman } from '@/api/http';
import tw from 'twin.macro';
import Select from '@/components/elements/Select';
import { ServerContext } from '@/state/server';
import { Button } from '@/components/elements/button/index';
import { Actions, useStoreActions } from 'easy-peasy';
import { Field as FormikField, Form, Formik } from 'formik';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import Field from '@/components/elements/Field';
import { ApplicationStore } from '@/state';
import { object, string } from 'yup';
import FormikFieldWrapper from '@/components/elements/FormikFieldWrapper';
import Modal from '@/components/elements/Modal';
import GreyRowBox from '@/components/elements/GreyRowBox';
import CopyOnClick from '@/components/elements/CopyOnClick';
import FlashMessageRender from '@/components/FlashMessageRender';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faGlobe } from '@fortawesome/free-solid-svg-icons';

type Subdomain = {
    id?: number;
    domain_id: number;
    domain?: Domain;
    subdomain?: string;
    allocation?: Allocation;
    allocation_id: number;
};

interface Domain {
    id: number;
    domain: string;
}

interface Values {
    allocation_id: number;
    domain: number;
    subdomain: string;
}

const NewSubdomainForm = ({ subDomains, setSubDomains, domains, availableAllocations, setShowCreate }: { subDomains: Subdomain[]; setSubDomains: (subDomains: Subdomain[]) => void; domains: Domain[]; availableAllocations: Allocation[]; setShowCreate: (show: boolean) => void }) => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addError, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);
    const [isSubmitting, setSubmitting] = React.useState(false);

    const submit = ({ allocation_id, domain, subdomain }: Values) => {
        clearFlashes('server:subdomain');
        setSubmitting(true);
        http.post(`/api/client/servers/${uuid}/domains`, {
            allocation_id: allocation_id,
            domain: domain,
            subdomain: subdomain,
        })
            .then(({ data }) => {
                setSubDomains([...subDomains, data.subdomain as Subdomain]);
                setSubmitting(false);
                setShowCreate(false);
            })
            .catch(error => {
                console.error(error);
                addError({ key: 'server:subdomain', message: httpErrorToHuman(error) });
                setSubmitting(false);
            });
    };
    

    return (
        <Formik
            onSubmit={submit}
            initialValues={{ 
                allocation_id: availableAllocations.length > 0 ? availableAllocations[0].id : 0, 
                domain: domains.length > 0 ? domains[0].id : 0, 
                subdomain: '' 
            }}
            validationSchema={object().shape({
                subdomain: string()
                    .required('A subdomain is required.')
                    .min(1, 'A subdomain must be at least 1 character long.')
                    .max(63, 'A subdomain must be less than 63 characters long.')
                    .matches(
                        /^[a-zA-Z0-9](?:[a-zA-Z0-9.-]*[a-zA-Z0-9])?$/,
                        'A subdomain can only contain alphanumeric characters, dots, and dashes, and cannot start or end with a dot.'
                    ),
            })}
        >
            <>
            <SpinnerOverlay visible={isSubmitting} />
            <FlashMessageRender byKey={'server:subdomain'} css={tw`mb-4`} />
            <Form className={'mt-6 flex flex-col space-y-6'}>
                <Field id={'subdomain'} name={'subdomain'} label={'Subdomain'} />
                <FormikFieldWrapper name={'domain'} label={'Domain'}>
                    <FormikField as={Select} id='domain' name={'domain'} className='w-full'>
                        {domains.map((domain) => (
                            <option key={`${domain.id}`} value={`${domain.id}`}>
                                {domain.domain}
                            </option>
                        ))}
                    </FormikField>
                </FormikFieldWrapper>
                <FormikFieldWrapper name={'allocation_id'} label={'Allocation'}>
                    <FormikField as={Select} id='allocation' name={'allocation_id'} className='w-full'>
                        {availableAllocations.map((allocation) => (
                            <option key={`${allocation.id}`} value={`${allocation.id}`}>
                                {allocation.ip}:{allocation.port}
                            </option>
                        ))}
                    </FormikField>
                </FormikFieldWrapper>
                <div css={tw`mt-6 w-full`}>
                    <Button type={'submit'}>
                        Create Subdomain
                    </Button>
                </div>
            </Form>
            </>
        </Formik>
    );
}

export default () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const [subDomains, setSubDomains] = React.useState<Subdomain[]>([]);
    const [domains, setDomains] = React.useState<Domain[]>([]);
    const { addError, addFlash } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);
    const [loading, setLoading] = React.useState(true);
    const [showCreate, setShowCreate] = React.useState(false);
    const [availableAllocations, setAvailableAllocations] = React.useState<Allocation[]>([]);
    const allocations = ServerContext.useStoreState((state) => state.server.data!.allocations);

    useEffect(() => {
        if(!subDomains || subDomains.length === 0) {
            setAvailableAllocations(allocations);
            return;
        }
        console.log(allocations, subDomains);
        setAvailableAllocations(allocations.filter((allocation) => !subDomains.find((subDomain) => subDomain.allocation_id === allocation.id)));
    }, [allocations, subDomains]);

    useEffect(() => {
        http.get(`/api/client/servers/${uuid}/domains`)
            .then((data) => {
                setSubDomains(data.data.subdomain);
                setDomains(data.data.domains);
                console.log(subDomains, domains, data.data);
                setLoading(false);
            })
            .catch(error => {
                console.error(error);
                addError({ key: 'domains', message: httpErrorToHuman(error) });
                setLoading(false);
            });
    }, []);
    const [isDeleting, setIsDeleting] = React.useState(false);
    function deleteSubdomain(subdomain: Subdomain) {
        setIsDeleting(true);
        http.delete(`/api/client/servers/${uuid}/domains/${subdomain.id}`)
            .then(() => {
                setSubDomains(subDomains.filter((s) => s.id !== subdomain.id));
                addFlash({ key: 'domains', message: 'Successfully deleted subdomain.', type: 'success' });
                setIsDeleting(false);
            })
            .catch(error => {
                console.error(error);
                addError({ key: 'domains', message: httpErrorToHuman(error) });
                setIsDeleting(false);
            });
    }

    return (
        <ServerContentBlock title={'Subdomains'}>
            <SpinnerOverlay size={'large'} visible={loading} />
            <SpinnerOverlay size={'large'} visible={isDeleting} />
            <FlashMessageRender byKey={'domains'} css={tw`mb-4`} />
            {!loading &&
                <>
                    <div>
                        <div css={tw`flex items-center justify-between`}>
                            <p css={tw`text-sm text-neutral-400`}>
                                Subdomains allows you to create custom subdomains for your server. Each subdomain can be assigned to a specific allocation on your server.
                                <br />
                                Your player can connect to your server using the subdomain you create here.
                            </p>
                            <Button
                                onClick={() => setShowCreate(true)}
                                disabled={availableAllocations.length === 0}
                            >
                                Create subdomain
                            </Button>
                        </div>
                        <div css={tw`mt-6`}>
                            {subDomains && subDomains.length > 0 ?
                                subDomains.map((subdomain) => (
                                    <GreyRowBox key={subdomain.id} $hoverable={false} className={'flex-wrap md:flex-nowrap mt-2 justify-between'}>
                                        <div className={'pl-4 pr-6 text-neutral-400'}>
                                            <FontAwesomeIcon icon={faGlobe} />
                                        </div>
                                        <div className={'flex flex-col md:flex-row items-center w-full'}>
                                            <div className={'w-fit md:mr-6'}>
                                                <Label>Subdomain</Label>
                                                <CopyOnClick text={subdomain.subdomain + '.' + subdomain.domain?.domain}>
                                                    <Code dark>{subdomain.subdomain + '.' + subdomain.domain?.domain}</Code>
                                                </CopyOnClick>

                                            </div>
                                            <div className={'w-fit'}>
                                                <Label>Allocation</Label>
                                                <Code dark>{subdomain.allocation?.ip + ':' + subdomain.allocation?.port}</Code>
                                            </div>
                                        </div>
                                        <div className={'flex items-center w-full md:w-auto'} onClick={() => deleteSubdomain(subdomain)}>
                                            <Button.Danger>
                                                Delete
                                            </Button.Danger>
                                        </div>
                                    </GreyRowBox>
                                ))
                                :
                                <p css={tw`text-sm text-neutral-400`}>
                                    You do not have any subdomains configured for this server.
                                </p>
                            }
                        </div>
                    </div>
                    <Modal visible={showCreate} onDismissed={() => setShowCreate(false)}>
                        <NewSubdomainForm subDomains={subDomains} domains={domains} availableAllocations={availableAllocations} setSubDomains={setSubDomains} setShowCreate={setShowCreate} />
                    </Modal>
                </>
            }
        </ServerContentBlock>
    );
}
