--
-- PostgreSQL database dump
--

\restrict iXUkdfHvMcjUPTCgROAMnCtbkzybeqF3sEGNOKHWdiY1el5nh3R5UpeBcumOx8h

-- Dumped from database version 14.23 (Ubuntu 14.23-0ubuntu0.22.04.1)
-- Dumped by pg_dump version 14.23 (Ubuntu 14.23-0ubuntu0.22.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: ad_packages; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.ad_packages (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255) NOT NULL,
    duration_days integer NOT NULL,
    price numeric(10,2) NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.ad_packages OWNER TO migrant_user;

--
-- Name: ad_packages_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.ad_packages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ad_packages_id_seq OWNER TO migrant_user;

--
-- Name: ad_packages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.ad_packages_id_seq OWNED BY public.ad_packages.id;


--
-- Name: advertisements; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.advertisements (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    ad_package_id bigint NOT NULL,
    type character varying(255) NOT NULL,
    job_id bigint,
    title character varying(255),
    image_url character varying(255),
    target_url character varying(255),
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    starts_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    impressions_count integer DEFAULT 0 NOT NULL,
    clicks_count integer DEFAULT 0 NOT NULL,
    admin_note text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT advertisements_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'active'::character varying, 'paused'::character varying, 'expired'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.advertisements OWNER TO migrant_user;

--
-- Name: advertisements_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.advertisements_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.advertisements_id_seq OWNER TO migrant_user;

--
-- Name: advertisements_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.advertisements_id_seq OWNED BY public.advertisements.id;


--
-- Name: application_status_history; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.application_status_history (
    id bigint NOT NULL,
    application_id bigint NOT NULL,
    verified_badge_status character varying(50),
    ready_to_work_status character varying(50),
    sponsorship_required boolean,
    employer_self_check_required boolean,
    worker_nationality character varying(100),
    worker_type_slug character varying(100),
    recorded_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    recorded_by bigint
);


ALTER TABLE public.application_status_history OWNER TO migrant_user;

--
-- Name: application_status_history_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.application_status_history_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.application_status_history_id_seq OWNER TO migrant_user;

--
-- Name: application_status_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.application_status_history_id_seq OWNED BY public.application_status_history.id;


--
-- Name: application_status_logs; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.application_status_logs (
    id bigint NOT NULL,
    application_id bigint NOT NULL,
    status character varying(30) NOT NULL,
    notes character varying(500),
    changed_by character varying(20) DEFAULT 'system'::character varying NOT NULL,
    changed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.application_status_logs OWNER TO migrant_user;

--
-- Name: application_status_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.application_status_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.application_status_logs_id_seq OWNER TO migrant_user;

--
-- Name: application_status_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.application_status_logs_id_seq OWNED BY public.application_status_logs.id;


--
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.audit_logs (
    id bigint NOT NULL,
    admin_id bigint,
    action character varying(255) NOT NULL,
    model_type character varying(255),
    model_id bigint,
    old_values json,
    new_values json,
    ip_address character varying(45),
    user_agent text,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.audit_logs OWNER TO migrant_user;

--
-- Name: audit_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.audit_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.audit_logs_id_seq OWNER TO migrant_user;

--
-- Name: audit_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.audit_logs_id_seq OWNED BY public.audit_logs.id;


--
-- Name: blocked_users; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.blocked_users (
    id bigint NOT NULL,
    blocker_id bigint NOT NULL,
    blocked_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.blocked_users OWNER TO migrant_user;

--
-- Name: blocked_users_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.blocked_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.blocked_users_id_seq OWNER TO migrant_user;

--
-- Name: blocked_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.blocked_users_id_seq OWNED BY public.blocked_users.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO migrant_user;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO migrant_user;

--
-- Name: categories; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.categories (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    icon character varying(255),
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.categories OWNER TO migrant_user;

--
-- Name: categories_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.categories_id_seq OWNER TO migrant_user;

--
-- Name: categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.categories_id_seq OWNED BY public.categories.id;


--
-- Name: chat_conversations; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.chat_conversations (
    id bigint NOT NULL,
    user_a_id bigint NOT NULL,
    user_b_id bigint NOT NULL,
    is_closed boolean DEFAULT false NOT NULL,
    closed_by bigint,
    closed_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.chat_conversations OWNER TO migrant_user;

--
-- Name: chat_conversations_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.chat_conversations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.chat_conversations_id_seq OWNER TO migrant_user;

--
-- Name: chat_conversations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.chat_conversations_id_seq OWNED BY public.chat_conversations.id;


--
-- Name: chat_messages; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.chat_messages (
    id bigint NOT NULL,
    sender_id bigint NOT NULL,
    receiver_id bigint NOT NULL,
    message text,
    is_read boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    type character varying(255) DEFAULT 'text'::character varying NOT NULL,
    attachment_url character varying(255),
    attachment_name character varying(255),
    attachment_size integer,
    detected_language character varying(10),
    translated_message text,
    translated_language character varying(255),
    cv_data text,
    job_id bigint,
    application_id bigint,
    CONSTRAINT chat_messages_type_check CHECK (((type)::text = ANY ((ARRAY['text'::character varying, 'image'::character varying, 'video'::character varying, 'file'::character varying])::text[])))
);


ALTER TABLE public.chat_messages OWNER TO migrant_user;

--
-- Name: chat_messages_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.chat_messages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.chat_messages_id_seq OWNER TO migrant_user;

--
-- Name: chat_messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.chat_messages_id_seq OWNED BY public.chat_messages.id;


--
-- Name: cities; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.cities (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    region character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.cities OWNER TO migrant_user;

--
-- Name: cities_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.cities_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.cities_id_seq OWNER TO migrant_user;

--
-- Name: cities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.cities_id_seq OWNED BY public.cities.id;


--
-- Name: document_types; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.document_types (
    id bigint NOT NULL,
    document_type_name character varying(100) NOT NULL,
    slug character varying(100) NOT NULL,
    description text,
    worker_type_id bigint,
    required_for_verified_badge boolean DEFAULT false NOT NULL,
    required_for_ready_to_work boolean DEFAULT false NOT NULL,
    verification_required boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.document_types OWNER TO migrant_user;

--
-- Name: document_types_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.document_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.document_types_id_seq OWNER TO migrant_user;

--
-- Name: document_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.document_types_id_seq OWNED BY public.document_types.id;


--
-- Name: employer_documents; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.employer_documents (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    document_type character varying(255) NOT NULL,
    document_url character varying(255) NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    review_note character varying(255),
    reviewed_by bigint,
    reviewed_at timestamp(0) without time zone,
    CONSTRAINT employer_documents_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'approved'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.employer_documents OWNER TO migrant_user;

--
-- Name: employer_documents_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.employer_documents_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.employer_documents_id_seq OWNER TO migrant_user;

--
-- Name: employer_documents_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.employer_documents_id_seq OWNED BY public.employer_documents.id;


--
-- Name: employer_staff; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.employer_staff (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    agency_employer_id bigint NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    approved_at timestamp(0) without time zone,
    approved_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT employer_staff_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'approved'::character varying, 'rejected'::character varying, 'suspended'::character varying])::text[])))
);


ALTER TABLE public.employer_staff OWNER TO migrant_user;

--
-- Name: employer_staff_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.employer_staff_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.employer_staff_id_seq OWNER TO migrant_user;

--
-- Name: employer_staff_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.employer_staff_id_seq OWNED BY public.employer_staff.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO migrant_user;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.failed_jobs_id_seq OWNER TO migrant_user;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: industries; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.industries (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.industries OWNER TO migrant_user;

--
-- Name: industries_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.industries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.industries_id_seq OWNER TO migrant_user;

--
-- Name: industries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.industries_id_seq OWNED BY public.industries.id;


--
-- Name: job_applications; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.job_applications (
    id bigint NOT NULL,
    job_id bigint NOT NULL,
    user_id bigint NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    cover_letter text,
    applied_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    employer_notes text,
    status_snapshot_id bigint,
    CONSTRAINT job_applications_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'viewed'::character varying, 'reviewed'::character varying, 'shortlisted'::character varying, 'accepted'::character varying, 'rejected'::character varying, 'cancelled'::character varying])::text[])))
);


ALTER TABLE public.job_applications OWNER TO migrant_user;

--
-- Name: job_applications_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.job_applications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.job_applications_id_seq OWNER TO migrant_user;

--
-- Name: job_applications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.job_applications_id_seq OWNED BY public.job_applications.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO migrant_user;

--
-- Name: job_listings; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.job_listings (
    id bigint NOT NULL,
    employer_id bigint NOT NULL,
    title character varying(255) NOT NULL,
    employer_name character varying(255) NOT NULL,
    employer_type character varying(255) NOT NULL,
    location character varying(255) NOT NULL,
    salary character varying(255) NOT NULL,
    salary_period character varying(255) DEFAULT 'Month'::character varying NOT NULL,
    tags json,
    category character varying(255) NOT NULL,
    description text,
    duties text,
    requirements text,
    benefits text,
    is_urgent boolean DEFAULT false NOT NULL,
    status character varying(30) DEFAULT 'published'::character varying NOT NULL,
    risk_level character varying(20) DEFAULT 'low'::character varying NOT NULL,
    posted_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    hours character varying(255),
    language character varying(255),
    legal_status character varying(255),
    eligibility character varying(255) DEFAULT 'Unknown'::character varying NOT NULL,
    verification_required boolean DEFAULT true NOT NULL,
    job_type_id bigint,
    rejection_reason text,
    employment_type character varying(255),
    working_hours_and_rest_days text,
    worker_count integer,
    employment_period character varying(255),
    dormitory_meals_deductions text,
    contact_method character varying(255),
    mask_contact_info boolean DEFAULT false NOT NULL,
    employer_authorization_url character varying(255),
    job_source_proof_url character varying(255),
    fee_table_url character varying(255),
    red_flags json,
    missing_fields json,
    screened_at timestamp(0) without time zone,
    is_sponsored boolean DEFAULT false NOT NULL,
    sponsored_until timestamp(0) without time zone,
    CONSTRAINT job_listings_employer_type_check CHECK (((employer_type)::text = ANY ((ARRAY['company'::character varying, 'factory'::character varying, 'family_care'::character varying, 'agency'::character varying])::text[]))),
    CONSTRAINT job_listings_risk_level_check CHECK (((risk_level)::text = ANY ((ARRAY['low'::character varying, 'medium'::character varying, 'high'::character varying, 'critical'::character varying])::text[]))),
    CONSTRAINT job_listings_salary_period_check CHECK (((salary_period)::text = ANY ((ARRAY['Month'::character varying, 'Day'::character varying, 'Hour'::character varying])::text[]))),
    CONSTRAINT job_listings_status_check CHECK (((status)::text = ANY ((ARRAY['draft'::character varying, 'submitted_for_review'::character varying, 'published'::character varying, 'paused'::character varying, 'closed'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.job_listings OWNER TO migrant_user;

--
-- Name: job_listings_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.job_listings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.job_listings_id_seq OWNER TO migrant_user;

--
-- Name: job_listings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.job_listings_id_seq OWNED BY public.job_listings.id;


--
-- Name: job_types; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.job_types (
    id bigint NOT NULL,
    job_type_name character varying(100) NOT NULL,
    slug character varying(100) NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.job_types OWNER TO migrant_user;

--
-- Name: job_types_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.job_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.job_types_id_seq OWNER TO migrant_user;

--
-- Name: job_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.job_types_id_seq OWNED BY public.job_types.id;


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO migrant_user;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.jobs_id_seq OWNER TO migrant_user;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: languages; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.languages (
    id bigint NOT NULL,
    language_code character varying(10) NOT NULL,
    language_name character varying(100) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.languages OWNER TO migrant_user;

--
-- Name: languages_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.languages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.languages_id_seq OWNER TO migrant_user;

--
-- Name: languages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.languages_id_seq OWNED BY public.languages.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO migrant_user;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.migrations_id_seq OWNER TO migrant_user;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: nationalities; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.nationalities (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(10),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.nationalities OWNER TO migrant_user;

--
-- Name: nationalities_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.nationalities_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.nationalities_id_seq OWNER TO migrant_user;

--
-- Name: nationalities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.nationalities_id_seq OWNED BY public.nationalities.id;


--
-- Name: notifications; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.notifications (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    type character varying(255) NOT NULL,
    title character varying(255) NOT NULL,
    body text NOT NULL,
    data json,
    read_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.notifications OWNER TO migrant_user;

--
-- Name: notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.notifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.notifications_id_seq OWNER TO migrant_user;

--
-- Name: notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.notifications_id_seq OWNED BY public.notifications.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO migrant_user;

--
-- Name: payments; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.payments (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    amount numeric(8,2) NOT NULL,
    payment_gateway character varying(255) DEFAULT 'mock'::character varying NOT NULL,
    transaction_id character varying(255),
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT payments_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'completed'::character varying, 'failed'::character varying])::text[])))
);


ALTER TABLE public.payments OWNER TO migrant_user;

--
-- Name: payments_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.payments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.payments_id_seq OWNER TO migrant_user;

--
-- Name: payments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.payments_id_seq OWNED BY public.payments.id;


--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name text NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_access_tokens OWNER TO migrant_user;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.personal_access_tokens_id_seq OWNER TO migrant_user;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: reports; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.reports (
    id bigint NOT NULL,
    reporter_id bigint NOT NULL,
    reported_id bigint,
    job_id bigint,
    chat_message_id bigint,
    report_type character varying(255) NOT NULL,
    reason character varying(255) NOT NULL,
    description text,
    evidence_url character varying(255),
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    admin_note text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    severity character varying(255) DEFAULT 'medium'::character varying NOT NULL,
    resolved_at timestamp(0) without time zone,
    CONSTRAINT reports_report_type_check CHECK (((report_type)::text = ANY (ARRAY[('user'::character varying)::text, ('job'::character varying)::text, ('chat'::character varying)::text]))),
    CONSTRAINT reports_severity_check CHECK (((severity)::text = ANY ((ARRAY['low'::character varying, 'medium'::character varying, 'high'::character varying, 'critical'::character varying])::text[]))),
    CONSTRAINT reports_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'in_review'::character varying, 'resolved'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.reports OWNER TO migrant_user;

--
-- Name: reports_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.reports_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.reports_id_seq OWNER TO migrant_user;

--
-- Name: reports_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.reports_id_seq OWNED BY public.reports.id;


--
-- Name: safety_checks; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.safety_checks (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    source_type character varying(255) NOT NULL,
    source_id character varying(255),
    input_text text,
    image_url character varying(255),
    risk_level character varying(255) NOT NULL,
    result_json json NOT NULL,
    language character varying(30) DEFAULT 'English'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT safety_checks_risk_level_check CHECK (((risk_level)::text = ANY ((ARRAY['low'::character varying, 'medium'::character varying, 'high'::character varying, 'critical'::character varying])::text[]))),
    CONSTRAINT safety_checks_source_type_check CHECK (((source_type)::text = ANY ((ARRAY['job'::character varying, 'chat'::character varying, 'screenshot'::character varying])::text[])))
);


ALTER TABLE public.safety_checks OWNER TO migrant_user;

--
-- Name: safety_checks_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.safety_checks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.safety_checks_id_seq OWNER TO migrant_user;

--
-- Name: safety_checks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.safety_checks_id_seq OWNED BY public.safety_checks.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO migrant_user;

--
-- Name: skills; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.skills (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.skills OWNER TO migrant_user;

--
-- Name: skills_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.skills_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.skills_id_seq OWNER TO migrant_user;

--
-- Name: skills_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.skills_id_seq OWNED BY public.skills.id;


--
-- Name: subscriptions; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.subscriptions (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    plan_type character varying(255) NOT NULL,
    chat_translation_quota integer DEFAULT 100 NOT NULL,
    starts_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    expires_at timestamp(0) without time zone,
    status character varying(255) DEFAULT 'active'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT subscriptions_plan_type_check CHECK (((plan_type)::text = ANY ((ARRAY['daily'::character varying, 'weekly'::character varying, 'monthly'::character varying])::text[]))),
    CONSTRAINT subscriptions_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'expired'::character varying, 'cancelled'::character varying])::text[])))
);


ALTER TABLE public.subscriptions OWNER TO migrant_user;

--
-- Name: subscriptions_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.subscriptions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.subscriptions_id_seq OWNER TO migrant_user;

--
-- Name: subscriptions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.subscriptions_id_seq OWNED BY public.subscriptions.id;


--
-- Name: translation_logs; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.translation_logs (
    id bigint NOT NULL,
    chat_message_id bigint NOT NULL,
    user_id bigint NOT NULL,
    original_text text NOT NULL,
    translated_text text NOT NULL,
    source_language character varying(10),
    target_language character varying(10) NOT NULL,
    trigger_type character varying(255) DEFAULT 'manual'::character varying NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT translation_logs_trigger_type_check CHECK (((trigger_type)::text = ANY ((ARRAY['auto'::character varying, 'manual'::character varying])::text[])))
);


ALTER TABLE public.translation_logs OWNER TO migrant_user;

--
-- Name: translation_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.translation_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.translation_logs_id_seq OWNER TO migrant_user;

--
-- Name: translation_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.translation_logs_id_seq OWNED BY public.translation_logs.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    full_name character varying(255),
    role character varying(50),
    nationality character varying(255),
    current_city character varying(255),
    company_name character varying(255),
    industry character varying(255),
    profile_completed boolean DEFAULT false NOT NULL,
    avatar_url character varying(255),
    phone character varying(255),
    license_number character varying(255),
    verification_status character varying(255) DEFAULT 'unverified'::character varying NOT NULL,
    cv_url character varying(255),
    preferred_language character varying(255),
    phone_verified_at timestamp(0) without time zone,
    date_of_birth date,
    gender character varying(255),
    address text,
    educations json,
    work_experiences json,
    skills json,
    fcm_token character varying(255),
    verification_note text,
    is_admin boolean DEFAULT false NOT NULL,
    worker_type character varying(255),
    current_work_status character varying(255),
    language_abilities json,
    is_cv_public boolean DEFAULT true NOT NULL,
    onboarding_step smallint DEFAULT '1'::smallint NOT NULL,
    selfie_file_url character varying(500),
    selfie_verified_at timestamp(0) without time zone,
    verified_badge_status character varying(50) DEFAULT 'unverified'::character varying NOT NULL,
    verified_badge_updated_at timestamp(0) without time zone,
    ready_to_work_status character varying(50) DEFAULT 'not_ready'::character varying NOT NULL,
    ready_to_work_updated_at timestamp(0) without time zone,
    sponsorship_required boolean DEFAULT false NOT NULL,
    employer_self_check_required boolean DEFAULT false NOT NULL,
    available_date date,
    expected_salary numeric(10,2),
    worker_type_id bigint,
    unified_business_number character varying(50),
    sponsorship_status character varying(255),
    license_expiry_date date,
    trust_score integer DEFAULT 100 NOT NULL,
    violation_count integer DEFAULT 0 NOT NULL,
    provider_name character varying(255),
    provider_id character varying(255),
    notification_preferences json DEFAULT '{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}'::json,
    is_suspended boolean DEFAULT false NOT NULL,
    suspension_reason text,
    suspended_at timestamp(0) without time zone,
    CONSTRAINT users_role_check CHECK (((role)::text = ANY ((ARRAY['worker'::character varying, 'company'::character varying, 'factory'::character varying, 'family_care'::character varying, 'agency'::character varying, 'agency_staff'::character varying])::text[]))),
    CONSTRAINT users_verification_status_check CHECK (((verification_status)::text = ANY ((ARRAY['unverified'::character varying, 'pending'::character varying, 'basic_verified'::character varying, 'manually_verified'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.users OWNER TO migrant_user;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO migrant_user;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: verification_codes; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.verification_codes (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    type character varying(255) NOT NULL,
    target character varying(255) NOT NULL,
    code character varying(255) NOT NULL,
    expires_at timestamp(0) without time zone NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.verification_codes OWNER TO migrant_user;

--
-- Name: verification_codes_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.verification_codes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.verification_codes_id_seq OWNER TO migrant_user;

--
-- Name: verification_codes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.verification_codes_id_seq OWNED BY public.verification_codes.id;


--
-- Name: verification_logs; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.verification_logs (
    id bigint NOT NULL,
    entity_type character varying(50) NOT NULL,
    entity_id bigint NOT NULL,
    action character varying(50) NOT NULL,
    notes text,
    verified_by bigint NOT NULL,
    verified_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.verification_logs OWNER TO migrant_user;

--
-- Name: verification_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.verification_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.verification_logs_id_seq OWNER TO migrant_user;

--
-- Name: verification_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.verification_logs_id_seq OWNED BY public.verification_logs.id;


--
-- Name: violation_histories; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.violation_histories (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    report_id bigint,
    violation_type character varying(255) NOT NULL,
    description text,
    points_deducted integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.violation_histories OWNER TO migrant_user;

--
-- Name: violation_histories_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.violation_histories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.violation_histories_id_seq OWNER TO migrant_user;

--
-- Name: violation_histories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.violation_histories_id_seq OWNED BY public.violation_histories.id;


--
-- Name: worker_document_requirements; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.worker_document_requirements (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    document_type_id bigint NOT NULL,
    upload_status character varying(255) DEFAULT 'not_uploaded'::character varying NOT NULL,
    worker_document_id bigint,
    required_by_date date,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT worker_document_requirements_upload_status_check CHECK (((upload_status)::text = ANY ((ARRAY['not_uploaded'::character varying, 'uploaded'::character varying, 'verified'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.worker_document_requirements OWNER TO migrant_user;

--
-- Name: worker_document_requirements_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.worker_document_requirements_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.worker_document_requirements_id_seq OWNER TO migrant_user;

--
-- Name: worker_document_requirements_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.worker_document_requirements_id_seq OWNED BY public.worker_document_requirements.id;


--
-- Name: worker_documents; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.worker_documents (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    document_type_id bigint NOT NULL,
    file_url character varying(500) NOT NULL,
    original_filename character varying(255),
    review_status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    review_note text,
    reviewed_by bigint,
    reviewed_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    expiry_date date,
    CONSTRAINT worker_documents_review_status_check CHECK (((review_status)::text = ANY ((ARRAY['pending'::character varying, 'approved'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.worker_documents OWNER TO migrant_user;

--
-- Name: worker_documents_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.worker_documents_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.worker_documents_id_seq OWNER TO migrant_user;

--
-- Name: worker_documents_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.worker_documents_id_seq OWNED BY public.worker_documents.id;


--
-- Name: worker_job_types; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.worker_job_types (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    job_type_id bigint NOT NULL,
    years_of_experience smallint DEFAULT '0'::smallint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.worker_job_types OWNER TO migrant_user;

--
-- Name: worker_job_types_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.worker_job_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.worker_job_types_id_seq OWNER TO migrant_user;

--
-- Name: worker_job_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.worker_job_types_id_seq OWNED BY public.worker_job_types.id;


--
-- Name: worker_languages; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.worker_languages (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    language_id bigint NOT NULL,
    proficiency_level character varying(255) DEFAULT 'basic'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT worker_languages_proficiency_level_check CHECK (((proficiency_level)::text = ANY ((ARRAY['basic'::character varying, 'intermediate'::character varying, 'advanced'::character varying, 'fluent'::character varying])::text[])))
);


ALTER TABLE public.worker_languages OWNER TO migrant_user;

--
-- Name: worker_languages_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.worker_languages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.worker_languages_id_seq OWNER TO migrant_user;

--
-- Name: worker_languages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.worker_languages_id_seq OWNED BY public.worker_languages.id;


--
-- Name: worker_types; Type: TABLE; Schema: public; Owner: migrant_user
--

CREATE TABLE public.worker_types (
    id bigint NOT NULL,
    worker_type_name character varying(100) NOT NULL,
    slug character varying(100) NOT NULL,
    description text,
    requires_arc boolean DEFAULT true NOT NULL,
    auto_ready_to_work boolean DEFAULT false NOT NULL,
    eligible_to_work boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.worker_types OWNER TO migrant_user;

--
-- Name: worker_types_id_seq; Type: SEQUENCE; Schema: public; Owner: migrant_user
--

CREATE SEQUENCE public.worker_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.worker_types_id_seq OWNER TO migrant_user;

--
-- Name: worker_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: migrant_user
--

ALTER SEQUENCE public.worker_types_id_seq OWNED BY public.worker_types.id;


--
-- Name: ad_packages id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.ad_packages ALTER COLUMN id SET DEFAULT nextval('public.ad_packages_id_seq'::regclass);


--
-- Name: advertisements id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.advertisements ALTER COLUMN id SET DEFAULT nextval('public.advertisements_id_seq'::regclass);


--
-- Name: application_status_history id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.application_status_history ALTER COLUMN id SET DEFAULT nextval('public.application_status_history_id_seq'::regclass);


--
-- Name: application_status_logs id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.application_status_logs ALTER COLUMN id SET DEFAULT nextval('public.application_status_logs_id_seq'::regclass);


--
-- Name: audit_logs id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.audit_logs ALTER COLUMN id SET DEFAULT nextval('public.audit_logs_id_seq'::regclass);


--
-- Name: blocked_users id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.blocked_users ALTER COLUMN id SET DEFAULT nextval('public.blocked_users_id_seq'::regclass);


--
-- Name: categories id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.categories ALTER COLUMN id SET DEFAULT nextval('public.categories_id_seq'::regclass);


--
-- Name: chat_conversations id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.chat_conversations ALTER COLUMN id SET DEFAULT nextval('public.chat_conversations_id_seq'::regclass);


--
-- Name: chat_messages id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.chat_messages ALTER COLUMN id SET DEFAULT nextval('public.chat_messages_id_seq'::regclass);


--
-- Name: cities id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.cities ALTER COLUMN id SET DEFAULT nextval('public.cities_id_seq'::regclass);


--
-- Name: document_types id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.document_types ALTER COLUMN id SET DEFAULT nextval('public.document_types_id_seq'::regclass);


--
-- Name: employer_documents id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.employer_documents ALTER COLUMN id SET DEFAULT nextval('public.employer_documents_id_seq'::regclass);


--
-- Name: employer_staff id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.employer_staff ALTER COLUMN id SET DEFAULT nextval('public.employer_staff_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: industries id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.industries ALTER COLUMN id SET DEFAULT nextval('public.industries_id_seq'::regclass);


--
-- Name: job_applications id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.job_applications ALTER COLUMN id SET DEFAULT nextval('public.job_applications_id_seq'::regclass);


--
-- Name: job_listings id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.job_listings ALTER COLUMN id SET DEFAULT nextval('public.job_listings_id_seq'::regclass);


--
-- Name: job_types id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.job_types ALTER COLUMN id SET DEFAULT nextval('public.job_types_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: languages id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.languages ALTER COLUMN id SET DEFAULT nextval('public.languages_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: nationalities id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.nationalities ALTER COLUMN id SET DEFAULT nextval('public.nationalities_id_seq'::regclass);


--
-- Name: notifications id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.notifications ALTER COLUMN id SET DEFAULT nextval('public.notifications_id_seq'::regclass);


--
-- Name: payments id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.payments ALTER COLUMN id SET DEFAULT nextval('public.payments_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: reports id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.reports ALTER COLUMN id SET DEFAULT nextval('public.reports_id_seq'::regclass);


--
-- Name: safety_checks id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.safety_checks ALTER COLUMN id SET DEFAULT nextval('public.safety_checks_id_seq'::regclass);


--
-- Name: skills id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.skills ALTER COLUMN id SET DEFAULT nextval('public.skills_id_seq'::regclass);


--
-- Name: subscriptions id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.subscriptions ALTER COLUMN id SET DEFAULT nextval('public.subscriptions_id_seq'::regclass);


--
-- Name: translation_logs id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.translation_logs ALTER COLUMN id SET DEFAULT nextval('public.translation_logs_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: verification_codes id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.verification_codes ALTER COLUMN id SET DEFAULT nextval('public.verification_codes_id_seq'::regclass);


--
-- Name: verification_logs id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.verification_logs ALTER COLUMN id SET DEFAULT nextval('public.verification_logs_id_seq'::regclass);


--
-- Name: violation_histories id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.violation_histories ALTER COLUMN id SET DEFAULT nextval('public.violation_histories_id_seq'::regclass);


--
-- Name: worker_document_requirements id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_document_requirements ALTER COLUMN id SET DEFAULT nextval('public.worker_document_requirements_id_seq'::regclass);


--
-- Name: worker_documents id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_documents ALTER COLUMN id SET DEFAULT nextval('public.worker_documents_id_seq'::regclass);


--
-- Name: worker_job_types id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_job_types ALTER COLUMN id SET DEFAULT nextval('public.worker_job_types_id_seq'::regclass);


--
-- Name: worker_languages id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_languages ALTER COLUMN id SET DEFAULT nextval('public.worker_languages_id_seq'::regclass);


--
-- Name: worker_types id; Type: DEFAULT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_types ALTER COLUMN id SET DEFAULT nextval('public.worker_types_id_seq'::regclass);


--
-- Data for Name: ad_packages; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.ad_packages (id, name, type, duration_days, price, is_active, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: advertisements; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.advertisements (id, user_id, ad_package_id, type, job_id, title, image_url, target_url, status, starts_at, expires_at, impressions_count, clicks_count, admin_note, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: application_status_history; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.application_status_history (id, application_id, verified_badge_status, ready_to_work_status, sponsorship_required, employer_self_check_required, worker_nationality, worker_type_slug, recorded_at, recorded_by) FROM stdin;
1	7	verified	not_ready	f	f	info	student	2026-06-15 14:14:19	\N
2	8	verified	ready	t	f	Wus	white_collar	2026-06-16 00:55:28	\N
3	9	verified	ready	f	f	Indonesia	arc_other	2026-06-18 02:07:23	\N
4	10	verified	pending	f	f	Indonesia	blue_collar	2026-06-19 04:15:15	\N
5	11	verified	pending	f	f	Indonesia	blue_collar	2026-06-19 06:33:48	\N
\.


--
-- Data for Name: application_status_logs; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.application_status_logs (id, application_id, status, notes, changed_by, changed_at) FROM stdin;
1	10	shortlisted	\N	employer	2026-06-24 13:23:44
2	10	accepted	\N	employer	2026-06-24 13:23:49
3	10	shortlisted	\N	employer	2026-06-25 06:20:16
4	10	accepted	\N	employer	2026-06-25 06:20:18
5	10	accepted	\N	employer	2026-06-25 06:20:24
6	9	shortlisted	\N	employer	2026-06-25 06:21:58
7	9	shortlisted	\N	employer	2026-06-25 06:22:10
8	9	accepted	\N	employer	2026-06-25 06:23:41
9	10	shortlisted	\N	employer	2026-06-25 06:25:26
\.


--
-- Data for Name: audit_logs; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.audit_logs (id, admin_id, action, model_type, model_id, old_values, new_values, ip_address, user_agent, description, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: blocked_users; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.blocked_users (id, blocker_id, blocked_id, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: categories; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.categories (id, name, slug, icon, description, created_at, updated_at) FROM stdin;
1	Manufacturing	manufacturing	precision_manufacturing	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
2	Construction	construction	construction	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
3	Domestic Care	domestic-care	home_health	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
4	Logistics	logistics	local_shipping	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
5	Agriculture	agriculture	agriculture	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
6	Fisheries	fisheries	sailing	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
7	Hospitality	hospitality	hotel	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
8	Technology	technology	computer	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
\.


--
-- Data for Name: chat_conversations; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.chat_conversations (id, user_a_id, user_b_id, is_closed, closed_by, closed_at, created_at, updated_at) FROM stdin;
1	20	30	f	\N	\N	2026-06-24 13:12:31	2026-06-24 13:12:31
2	30	32	f	\N	\N	2026-06-24 13:24:02	2026-06-24 13:24:02
\.


--
-- Data for Name: chat_messages; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.chat_messages (id, sender_id, receiver_id, message, is_read, created_at, updated_at, type, attachment_url, attachment_name, attachment_size, detected_language, translated_message, translated_language, cv_data, job_id, application_id) FROM stdin;
1	17	18	halo	t	2026-06-16 01:21:14	2026-06-16 01:21:56	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
68	30	20	ddr	t	2026-06-19 16:06:30	2026-06-19 16:08:04	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
2	30	20	halo	t	2026-06-18 02:33:05	2026-06-18 02:33:51	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
144	30	20	red	f	2026-06-24 13:12:31	2026-06-24 13:12:31	text	\N	\N	\N	\N	merah	indonesian	\N	\N	\N
3	20	30	halo	t	2026-06-18 02:33:54	2026-06-18 06:52:46	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
142	30	20	我喜歡起司	t	2026-06-20 20:06:31	2026-06-21 18:27:57	text	\N	\N	\N	zh	\N	\N	\N	\N	\N
4	30	20	ngent	t	2026-06-18 06:52:50	2026-06-18 06:52:55	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
5	20	30	asu	t	2026-06-18 06:53:01	2026-06-18 06:53:01	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
6	20	30	asu	t	2026-06-18 06:53:06	2026-06-18 06:53:48	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
7	20	30	hei	t	2026-06-18 06:53:20	2026-06-18 06:53:48	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
69	30	20	hn	t	2026-06-19 16:08:18	2026-06-19 16:10:01	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
8	30	20	yyy	t	2026-06-18 06:53:51	2026-06-18 06:54:01	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
70	30	20	hm	t	2026-06-19 16:08:25	2026-06-19 16:10:01	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
9	30	20	tt	t	2026-06-18 06:54:07	2026-06-18 06:54:08	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
73	30	20	hjs	t	2026-06-19 16:08:51	2026-06-19 16:10:01	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
10	20	30	haja	t	2026-06-18 06:54:18	2026-06-18 06:54:19	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
71	20	30	hn	t	2026-06-19 16:08:29	2026-06-19 16:18:06	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
12	30	20	tot	t	2026-06-18 06:54:44	2026-06-18 06:54:48	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
72	20	30	v	t	2026-06-19 16:08:46	2026-06-19 16:18:06	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
74	30	20	hbh	t	2026-06-19 16:18:12	2026-06-19 16:18:16	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
17	30	32	testing	t	2026-06-19 04:20:56	2026-06-19 04:21:55	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
18	30	32	hello	t	2026-06-19 04:21:20	2026-06-19 04:21:55	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
15	30	20	\N	t	2026-06-18 08:53:04	2026-06-19 07:01:51	image	/storage/chats/6073FcdRNPr0TIY2kUeVUrbzwvrvOLDsC9z7idkk.jpg	1000039917_compressed.jpg	79999	\N	\N	\N	\N	\N	\N
16	30	20	\N	t	2026-06-18 09:00:31	2026-06-19 07:01:51	file	http://130.94.34.24/storage/chats/JLYnrC6g84XU8xY1q74OyC95WtQuQePWhOYGG1z4.pdf	perubahan-harga-produk-irs 18 juni 2026.pdf	1558250	\N	\N	\N	\N	\N	\N
19	20	30	hei	t	2026-06-19 07:03:33	2026-06-19 07:04:03	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
76	30	20	hh	t	2026-06-19 16:18:29	2026-06-19 16:18:53	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
20	30	20	testing	t	2026-06-19 07:04:08	2026-06-19 07:04:42	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
21	30	20	tttt	t	2026-06-19 07:04:28	2026-06-19 07:04:42	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
78	30	20	g	t	2026-06-19 16:28:43	2026-06-19 16:36:59	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
22	20	30	kontol	t	2026-06-19 07:04:45	2026-06-19 07:13:20	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
23	20	30	danil	t	2026-06-19 07:05:07	2026-06-19 07:13:20	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
24	30	20	testing	t	2026-06-19 07:13:33	2026-06-19 07:14:08	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
25	30	20	jam e kok 7.13 yak	t	2026-06-19 07:13:40	2026-06-19 07:14:08	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
75	20	30	h	t	2026-06-19 16:18:24	2026-06-19 17:13:51	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
26	20	30	nah ku2i	t	2026-06-19 07:14:11	2026-06-19 07:15:03	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
77	20	30	vf	t	2026-06-19 16:27:45	2026-06-19 17:13:51	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
79	20	30	r	t	2026-06-19 16:37:05	2026-06-19 17:13:51	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
27	30	20	eh	t	2026-06-19 07:19:51	2026-06-19 07:21:24	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
28	30	20	iki masuk	t	2026-06-19 07:19:55	2026-06-19 07:21:24	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
29	30	20	haruse	t	2026-06-19 07:19:58	2026-06-19 07:21:24	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
81	30	20	bs	t	2026-06-19 17:13:54	2026-06-19 17:16:18	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
30	20	30	ujhh	t	2026-06-19 07:21:28	2026-06-19 07:21:51	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
31	20	30	yut	t	2026-06-19 07:21:45	2026-06-19 07:21:51	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
82	30	20	halo	t	2026-06-19 17:15:41	2026-06-19 17:16:18	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
32	20	30	as	t	2026-06-19 07:28:06	2026-06-19 07:28:50	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
33	30	20	kontolodon	t	2026-06-19 07:28:56	2026-06-19 07:36:46	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
34	30	20	wis	t	2026-06-19 07:29:44	2026-06-19 07:36:46	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
35	30	20	han	t	2026-06-19 07:29:53	2026-06-19 07:36:46	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
83	30	20	halo	t	2026-06-19 17:16:28	2026-06-19 17:17:23	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
84	20	30	aa	t	2026-06-19 17:16:36	2026-06-19 17:17:23	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
80	32	30	testing	t	2026-06-19 16:57:22	2026-06-20 03:56:47	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
38	20	30	g	t	2026-06-19 07:37:03	2026-06-19 07:38:43	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
58	32	30	yap	t	2026-06-19 13:58:48	2026-06-20 03:56:47	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
39	20	30	y	t	2026-06-19 07:39:01	2026-06-19 07:43:23	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
40	20	30	h	t	2026-06-19 07:43:19	2026-06-19 07:43:23	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
36	30	20	re	t	2026-06-19 07:36:51	2026-06-19 07:45:00	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
37	30	20	vh	t	2026-06-19 07:36:56	2026-06-19 07:45:00	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
42	30	20	s	t	2026-06-19 07:43:35	2026-06-19 07:45:00	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
43	30	20	c	t	2026-06-19 07:43:41	2026-06-19 07:45:00	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
41	20	30	g	t	2026-06-19 07:43:28	2026-06-19 07:45:38	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
44	20	30	h	t	2026-06-19 07:45:15	2026-06-19 07:45:38	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
45	30	20	j	t	2026-06-19 07:45:19	2026-06-19 07:48:00	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
46	20	30	u	t	2026-06-19 07:50:41	2026-06-19 07:51:09	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
47	20	30	y	t	2026-06-19 08:05:07	2026-06-19 08:09:45	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
48	30	20	d	t	2026-06-19 08:05:13	2026-06-19 08:10:12	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
49	30	20	ad	t	2026-06-19 08:09:58	2026-06-19 08:10:12	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
50	20	30	y	t	2026-06-19 08:10:15	2026-06-19 08:24:49	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
51	20	30	s	t	2026-06-19 08:10:24	2026-06-19 08:24:49	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
52	20	30	hv	t	2026-06-19 08:10:52	2026-06-19 08:24:49	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
53	20	30	h	t	2026-06-19 08:11:57	2026-06-19 08:24:49	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
54	30	20	dd	t	2026-06-19 08:23:16	2026-06-19 08:33:07	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
55	30	20	r	t	2026-06-19 08:24:52	2026-06-19 08:33:07	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
56	30	20	te	t	2026-06-19 08:35:51	2026-06-19 08:37:46	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
57	20	30	tt	t	2026-06-19 08:42:28	2026-06-19 15:50:50	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
59	30	20	hahah	t	2026-06-19 15:50:59	2026-06-19 15:51:05	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
60	20	30	hbv	t	2026-06-19 15:51:09	2026-06-19 15:54:33	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
62	30	20	d	t	2026-06-19 15:54:43	2026-06-19 15:55:10	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
63	30	20	x	t	2026-06-19 15:54:54	2026-06-19 15:55:10	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
65	30	20	ddd	t	2026-06-19 15:55:04	2026-06-19 15:55:10	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
61	20	30	h	t	2026-06-19 15:54:38	2026-06-19 15:55:11	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
64	20	30	gh	t	2026-06-19 15:54:56	2026-06-19 15:55:11	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
66	30	20	d	t	2026-06-19 15:55:16	2026-06-19 16:06:12	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
67	30	20	d	t	2026-06-19 15:55:22	2026-06-19 16:06:12	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
85	30	20	gh	t	2026-06-19 17:16:40	2026-06-19 17:17:23	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
139	20	30	assalamualaikum	t	2026-06-20 19:29:12	2026-06-21 02:39:51	text	\N	\N	\N	ar	\N	\N	\N	\N	\N
138	20	30	assalamualaikum	t	2026-06-20 19:27:00	2026-06-21 02:39:54	text	\N	\N	\N	ar	\N	\N	\N	\N	\N
137	20	30	waalaikumsalam	t	2026-06-20 19:24:56	2026-06-21 02:39:58	text	\N	\N	\N	ms	\N	\N	\N	\N	\N
136	30	20	assalamualaikum	t	2026-06-20 19:24:51	2026-06-21 02:40:02	text	\N	\N	\N	ar	\N	\N	\N	\N	\N
87	30	20	ff	t	2026-06-19 17:17:33	2026-06-19 17:17:47	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
89	30	20	bj	t	2026-06-19 17:17:42	2026-06-19 17:17:47	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
86	20	30	as	t	2026-06-19 17:17:28	2026-06-19 17:17:47	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
88	20	30	as	t	2026-06-19 17:17:40	2026-06-19 17:17:47	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
135	20	30	waalaikumsalam	t	2026-06-20 19:24:21	2026-06-21 02:40:05	text	\N	\N	\N	ms	\N	\N	\N	\N	\N
140	30	20	i'm from Taiwan	t	2026-06-20 19:53:46	2026-06-21 18:27:57	text	\N	\N	\N	en	\N	\N	\N	\N	\N
141	30	20	aku adalah orang indonesia	t	2026-06-20 20:03:30	2026-06-21 18:27:57	text	\N	\N	\N	id	\N	\N	\N	\N	\N
143	20	30	halo	t	2026-06-21 18:28:16	2026-06-21 21:45:47	text	\N	\N	\N	\N	สวัสดี	th	\N	\N	\N
145	30	32	hf	f	2026-06-24 13:24:02	2026-06-24 13:24:02	text	\N	\N	\N	\N	hf	Taiwanese (台語)	\N	11	10
91	20	30	as	t	2026-06-19 17:18:03	2026-06-20 01:23:14	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
92	20	30	aw	t	2026-06-19 17:18:15	2026-06-20 01:23:14	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
94	20	30	ff	t	2026-06-19 17:18:21	2026-06-20 01:23:14	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
95	20	30	df	t	2026-06-19 17:18:26	2026-06-20 01:23:14	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
90	30	20	as	t	2026-06-19 17:17:56	2026-06-20 03:55:30	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
93	30	20	cf	t	2026-06-19 17:18:20	2026-06-20 03:55:30	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
96	30	20	halo saya adalah	t	2026-06-20 01:23:22	2026-06-20 03:55:30	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
97	20	30	test bro	t	2026-06-20 03:56:48	2026-06-20 03:56:50	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
98	30	20	si ndut	t	2026-06-20 03:56:59	2026-06-20 03:58:07	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
99	20	30	aaaa	t	2026-06-20 03:58:17	2026-06-20 03:58:33	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
100	30	20	si ndut	t	2026-06-20 03:58:51	2026-06-20 04:22:18	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
101	30	20	sj	t	2026-06-20 03:59:16	2026-06-20 04:22:18	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
103	20	30	a	t	2026-06-20 04:22:59	2026-06-20 04:23:12	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
104	20	30	r	t	2026-06-20 04:23:03	2026-06-20 04:23:12	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
102	30	20	a	t	2026-06-20 04:22:54	2026-06-20 04:23:12	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
107	30	20	rerf	t	2026-06-20 04:23:45	2026-06-20 04:28:25	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
108	30	20	hg	t	2026-06-20 04:23:50	2026-06-20 04:28:25	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
105	20	30	a	t	2026-06-20 04:23:37	2026-06-20 04:28:26	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
106	20	30	rrrr	t	2026-06-20 04:23:43	2026-06-20 04:28:26	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
110	20	30	assss	t	2026-06-20 04:28:36	2026-06-20 06:06:55	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
109	30	20	abc	t	2026-06-20 04:28:31	2026-06-20 06:06:57	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
111	30	20	hfv	t	2026-06-20 06:07:01	2026-06-20 06:12:09	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
112	20	30	hf	t	2026-06-20 06:07:16	2026-06-20 06:12:26	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
113	20	30	assalamualaikum	t	2026-06-20 06:07:27	2026-06-20 06:12:26	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
115	20	30	ghf	t	2026-06-20 06:12:37	2026-06-20 06:12:45	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
116	20	30	yg	t	2026-06-20 06:12:43	2026-06-20 06:12:45	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
114	30	20	assalamualaikum	t	2026-06-20 06:12:33	2026-06-20 06:13:26	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
117	30	20	hjj	t	2026-06-20 06:12:48	2026-06-20 06:13:26	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
119	20	30	ggb	t	2026-06-20 06:13:35	2026-06-20 06:49:23	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
118	30	20	ghf	t	2026-06-20 06:13:31	2026-06-20 06:49:23	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
120	20	30	ress	t	2026-06-20 06:49:27	2026-06-20 06:49:39	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
121	30	20	resa	t	2026-06-20 06:49:32	2026-06-20 06:49:40	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
122	30	20	dddddd	t	2026-06-20 06:49:35	2026-06-20 06:49:40	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
123	20	30	abc	t	2026-06-20 18:49:08	2026-06-20 18:49:32	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
126	20	30	as	t	2026-06-20 18:49:51	2026-06-20 18:50:50	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
127	20	30	halo	t	2026-06-20 18:50:42	2026-06-20 18:50:50	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
124	30	20	hg	t	2026-06-20 18:49:37	2026-06-20 18:50:50	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
125	30	20	as	t	2026-06-20 18:49:41	2026-06-20 18:50:50	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
129	30	20	refg	t	2026-06-20 18:51:06	2026-06-20 18:51:15	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
128	20	30	assalamualaikum	t	2026-06-20 18:50:58	2026-06-20 18:51:15	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
130	30	20	gfg	t	2026-06-20 18:51:20	2026-06-20 18:51:49	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
133	30	20	gh	t	2026-06-20 18:51:44	2026-06-20 18:51:49	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
131	20	30	sdf	t	2026-06-20 18:51:25	2026-06-20 18:51:49	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
132	20	30	zx	t	2026-06-20 18:51:37	2026-06-20 18:51:49	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
134	30	20	assalamualaikum	t	2026-06-20 19:24:11	2026-06-20 19:24:43	text	\N	\N	\N	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: cities; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.cities (id, name, region, created_at, updated_at) FROM stdin;
1	Taipei	Northern Taiwan	2026-06-15 03:56:36	2026-06-15 03:56:36
2	New Taipei City	Northern Taiwan	2026-06-15 03:56:36	2026-06-15 03:56:36
3	Taoyuan	Northern Taiwan	2026-06-15 03:56:36	2026-06-15 03:56:36
4	Taichung	Central Taiwan	2026-06-15 03:56:36	2026-06-15 03:56:36
5	Tainan	Southern Taiwan	2026-06-15 03:56:36	2026-06-15 03:56:36
6	Kaohsiung	Southern Taiwan	2026-06-15 03:56:36	2026-06-15 03:56:36
7	Hsinchu	Northern Taiwan	2026-06-15 03:56:36	2026-06-15 03:56:36
8	Keelung	Northern Taiwan	2026-06-15 03:56:36	2026-06-15 03:56:36
9	Hualien	Eastern Taiwan	2026-06-15 03:56:36	2026-06-15 03:56:36
10	Taipei City	Northern Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
11	Taoyuan City	Northern Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
12	Taichung City	Central Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
13	Tainan City	Southern Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
14	Kaohsiung City	Southern Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
15	Keelung City	Northern Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
16	Hsinchu City	Northern Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
17	Chiayi City	Southern Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
18	Hsinchu County	Northern Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
19	Miaoli County	Central Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
20	Changhua County	Central Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
21	Nantou County	Central Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
22	Yunlin County	Central Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
23	Chiayi County	Southern Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
24	Pingtung County	Southern Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
25	Yilan County	Eastern Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
26	Hualien County	Eastern Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
27	Taitung County	Eastern Taiwan	2026-06-16 11:39:39	2026-06-16 11:39:39
28	Penghu County	Outlying Islands	2026-06-16 11:39:39	2026-06-16 11:39:39
29	Kinmen County	Outlying Islands	2026-06-16 11:39:39	2026-06-16 11:39:39
30	Lienchiang County	Outlying Islands	2026-06-16 11:39:39	2026-06-16 11:39:39
31	Other	Other	2026-06-16 11:39:39	2026-06-16 11:39:39
\.


--
-- Data for Name: document_types; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.document_types (id, document_type_name, slug, description, worker_type_id, required_for_verified_badge, required_for_ready_to_work, verification_required, created_at, updated_at) FROM stdin;
1	Personal ID / Passport	personal_id	National ID or passport copy	\N	t	f	t	2026-06-15 03:56:36	2026-06-15 03:56:36
2	Selfie Photo	selfie	Clear selfie photo for identity verification	\N	t	f	t	2026-06-15 03:56:36	2026-06-15 03:56:36
8	CV / Resume	cv	Curriculum Vitae or Resume	3	f	t	f	2026-06-15 03:56:36	2026-06-15 03:56:36
11	Business Registration	business_registration	Company registration certificate (for employers)	\N	f	f	t	2026-06-15 03:56:36	2026-06-15 03:56:36
3	Student Work Permit	student_work_permit	Ministry of Education / NIA issued student part-time work permit	1	f	t	t	2026-06-15 03:56:36	2026-06-15 03:56:36
4	Proof of Enrollment	enrollment_proof	Current semester school enrollment certificate	1	f	t	t	2026-06-15 03:56:36	2026-06-15 03:56:36
5	Transfer Document / CDC	transfer_document	Current employer permission for transfer (CDC or equivalent)	2	f	t	t	2026-06-15 03:56:36	2026-06-15 03:56:36
6	Work Contract	work_contract	Signed employment contract with current or future employer	2	f	f	f	2026-06-15 03:56:36	2026-06-15 03:56:36
7	Contract Ending Proof	contract_ending_proof	Document proving the contract is about to end	2	f	t	t	2026-06-15 03:56:36	2026-06-15 03:56:36
10	Work Permit	work_permit	Professional work permit issued by Ministry of Labor	3	f	t	t	2026-06-15 03:56:36	2026-06-15 03:56:36
9	Diploma / Degree Certificate	diploma	University or vocational graduation certificate	3	f	f	f	2026-06-15 03:56:36	2026-06-15 03:56:36
15	National ID Card	national_id	Taiwanese National Identification Card	6	t	f	t	2026-06-15 03:56:36	2026-06-15 03:56:36
16	Identity Proof	identity_proof	Valid passport or ARC	8	t	f	t	2026-06-15 03:56:36	2026-06-15 03:56:36
17	Business Proof / Tax Registration	company_registration	Proof of business or tax registration for companies	\N	t	f	t	2026-06-15 03:56:36	2026-06-15 03:56:36
12	Agency License	agency_license	Valid manpower agency license	\N	t	f	t	2026-06-15 03:56:36	2026-06-15 03:56:36
14	Agency Staff Card	agency_staff_card	Authorization card for agency staff	\N	t	f	t	2026-06-15 03:56:36	2026-06-15 03:56:36
13	Household Registration	family_employer_id	Household registration or ID for family employers	\N	t	f	t	2026-06-15 03:56:36	2026-06-15 03:56:36
\.


--
-- Data for Name: employer_documents; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.employer_documents (id, user_id, document_type, document_url, status, created_at, updated_at, review_note, reviewed_by, reviewed_at) FROM stdin;
1	15	agency_license	http://130.94.34.24/storage/employer_documents/doc_agency_license_15_1781511181.jpg	pending	2026-06-15 08:13:01	2026-06-15 08:13:01	\N	\N	\N
2	15	agency_staff_card	http://130.94.34.24/storage/employer_documents/doc_agency_staff_card_15_1781511181.jpg	pending	2026-06-15 08:13:01	2026-06-15 08:13:01	\N	\N	\N
4	16	agency_staff_card	http://130.94.34.24/storage/employer_documents/doc_agency_staff_card_16_1781511371.jpg	approved	2026-06-15 08:16:11	2026-06-15 08:32:17	\N	1	2026-06-15 08:32:17
3	16	agency_license	http://130.94.34.24/storage/employer_documents/doc_agency_license_16_1781511370.jpg	approved	2026-06-15 08:16:10	2026-06-15 08:32:17	\N	1	2026-06-15 08:32:17
5	17	company_registration	http://130.94.34.24/storage/employer_documents/doc_company_registration_17_1781531539.pdf	approved	2026-06-15 13:52:19	2026-06-15 13:53:40	\N	1	2026-06-15 13:53:40
6	17	company_registration	http://130.94.34.24/storage/employer_documents/doc_company_registration_17_1781531539.pdf	approved	2026-06-15 13:52:19	2026-06-15 13:53:44	\N	1	2026-06-15 13:53:44
7	26	agency_license	http://130.94.34.24/storage/employer_documents/doc_agency_license_26_1781620728.jpg	approved	2026-06-16 14:38:48	2026-06-16 14:39:09	\N	1	2026-06-16 14:39:09
8	26	agency_staff_card	http://130.94.34.24/storage/employer_documents/doc_agency_staff_card_26_1781620728.jpg	approved	2026-06-16 14:38:48	2026-06-16 14:39:12	\N	1	2026-06-16 14:39:12
9	27	company_registration	http://130.94.34.24/storage/employer_documents/doc_company_registration_27_1781625759.pdf	pending	2026-06-16 16:02:39	2026-06-16 16:02:39	\N	\N	\N
10	27	company_registration	http://130.94.34.24/storage/employer_documents/doc_company_registration_27_1781625760.pdf	pending	2026-06-16 16:02:40	2026-06-16 16:02:40	\N	\N	\N
12	30	contact_person_authorization	http://130.94.34.24/storage/employer_documents/doc_contact_person_authorization_30_1781748004.jpg	approved	2026-06-18 02:00:04	2026-06-18 02:04:11	\N	1	2026-06-18 02:04:11
11	30	factory_registration	http://130.94.34.24/storage/employer_documents/doc_factory_registration_30_1781748002.jpg	approved	2026-06-18 02:00:02	2026-06-18 02:04:11	\N	1	2026-06-18 02:04:11
13	33	company_registration	http://130.94.34.24/storage/employer_documents/doc_company_registration_33_1781848484.jpg	approved	2026-06-19 05:54:44	2026-06-19 05:54:59	\N	1	2026-06-19 05:54:59
14	33	company_registration	http://130.94.34.24/storage/employer_documents/doc_company_registration_33_1781848484.jpg	approved	2026-06-19 05:54:44	2026-06-19 05:54:59	\N	1	2026-06-19 05:54:59
15	34	care_recipient_id	http://130.94.34.24/storage/employer_documents/doc_care_recipient_id_34_1781849136.jpg	approved	2026-06-19 06:05:36	2026-06-22 14:41:31	\N	1	2026-06-22 14:41:31
16	34	basic_care_need_proof	http://130.94.34.24/storage/employer_documents/doc_basic_care_need_proof_34_1781849136.jpg	approved	2026-06-19 06:05:36	2026-06-22 14:41:31	\N	1	2026-06-22 14:41:31
17	34	relationship_proof	http://130.94.34.24/storage/employer_documents/doc_relationship_proof_34_1781849136.jpg	approved	2026-06-19 06:05:36	2026-06-22 14:41:31	\N	1	2026-06-22 14:41:31
\.


--
-- Data for Name: employer_staff; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.employer_staff (id, user_id, agency_employer_id, status, approved_at, approved_by, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: industries; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.industries (id, name, slug, created_at, updated_at) FROM stdin;
1	Manufacturing	manufacturing	2026-06-16 11:39:39	2026-06-16 11:39:39
2	Technology	technology	2026-06-16 11:39:39	2026-06-16 11:39:39
3	Construction	construction	2026-06-16 11:39:39	2026-06-16 11:39:39
4	Domestic Care	domestic-care	2026-06-16 11:39:39	2026-06-16 11:39:39
5	Agriculture	agriculture	2026-06-16 11:39:39	2026-06-16 11:39:39
6	Fisheries	fisheries	2026-06-16 11:39:39	2026-06-16 11:39:39
7	Hospitality	hospitality	2026-06-16 11:39:39	2026-06-16 11:39:39
8	Recruitment	recruitment	2026-06-16 11:39:39	2026-06-16 11:39:39
9	Other	other	2026-06-16 11:39:39	2026-06-16 11:39:39
\.


--
-- Data for Name: job_applications; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.job_applications (id, job_id, user_id, status, cover_letter, applied_at, created_at, updated_at, employer_notes, status_snapshot_id) FROM stdin;
1	1	2	reviewed	I have 2 years of manufacturing experience in the Philippines and am eager to work at TSMC.	2026-05-29 10:30:00	2026-06-15 03:56:39	2026-06-15 03:56:39	\N	\N
2	2	2	pending	I have experience caring for elderly family members and speak basic Mandarin.	2026-05-30 16:00:00	2026-06-15 03:56:39	2026-06-15 03:56:39	\N	\N
3	5	7	accepted	I worked in logistics for 3 years in Vietnam. I am ready for immediate start.	2026-05-29 14:00:00	2026-06-15 03:56:39	2026-06-15 03:56:39	\N	\N
4	4	8	pending	I have a technical diploma and experience with basic metalworking.	2026-05-28 09:00:00	2026-06-15 03:56:39	2026-06-15 03:56:39	\N	\N
5	6	8	reviewed	I have caregiving certification and can speak Mandarin conversationally.	2026-05-27 11:00:00	2026-06-15 03:56:39	2026-06-15 03:56:39	\N	\N
6	3	9	pending	I have 5 years of construction experience in Thailand.	2026-05-28 15:00:00	2026-06-15 03:56:39	2026-06-15 03:56:39	\N	\N
7	8	18	accepted	\N	2026-06-15 14:14:19	2026-06-15 14:14:19	2026-06-16 01:21:02	\N	1
8	8	19	accepted	testing	2026-06-16 00:55:28	2026-06-16 00:55:28	2026-06-16 01:29:18	\N	2
11	2	32	pending	\N	2026-06-19 06:33:48	2026-06-19 06:33:48	2026-06-19 06:33:48	\N	5
9	11	20	accepted	ajsnsnan	2026-06-18 02:07:23	2026-06-18 02:07:23	2026-06-25 06:23:41	\N	3
10	11	32	shortlisted	te	2026-06-19 04:15:15	2026-06-19 04:15:15	2026-06-25 06:25:26	\N	4
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: job_listings; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.job_listings (id, employer_id, title, employer_name, employer_type, location, salary, salary_period, tags, category, description, duties, requirements, benefits, is_urgent, status, risk_level, posted_at, expires_at, created_at, updated_at, hours, language, legal_status, eligibility, verification_required, job_type_id, rejection_reason, employment_type, working_hours_and_rest_days, worker_count, employment_period, dormitory_meals_deductions, contact_method, mask_contact_info, employer_authorization_url, job_source_proof_url, fee_table_url, red_flags, missing_fields, screened_at, is_sponsored, sponsored_until) FROM stdin;
1	4	Electronic Assembly Operator	TSMC	factory	Hsinchu	NT$ 35,000 - 45,000	Month	["Verified Factory","Dormitory Provided"]	Manufacturing	Join TSMC, the world's leading semiconductor foundry. We are looking for dedicated Electronic Assembly Operators to work in our state-of-the-art fabrication facilities in Hsinchu Science Park.	• Operate and monitor automated assembly equipment\n• Perform quality inspections on assembled components\n• Follow clean room protocols and safety procedures\n• Document production data and report anomalies\n• Participate in continuous improvement activities	• Minimum 1 year manufacturing experience\n• Ability to work rotating shifts (day/night)\n• Good eyesight and manual dexterity\n• Basic understanding of electronic components\n• Willingness to learn and follow SOPs	• Free dormitory accommodation\n• Meals provided (3 meals/day)\n• Health insurance coverage\n• Annual performance bonus\n• Overtime pay at 1.5x rate	f	published	low	2026-05-28 09:00:00	\N	2026-06-15 03:56:39	2026-06-15 03:56:39	\N	\N	\N	Unknown	t	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	\N	\N	\N	f	\N
2	5	Home Caregiver	Chen Family	family_care	Taipei	NT$ 28,000 + Overtime	Month	["Urgent","Food Included"]	Domestic Care	The Chen family is looking for a compassionate and responsible caregiver to help care for an elderly family member in their Taipei home. Live-in position with private room provided.	• Assist with daily living activities (bathing, dressing, feeding)\n• Administer medication on schedule\n• Accompany to medical appointments\n• Light housekeeping and meal preparation\n• Provide companionship and emotional support	• Previous caregiving experience preferred\n• Basic Mandarin communication skills\n• Patient and compassionate personality\n• Willingness to live-in\n• Valid ARC (Alien Resident Certificate)	• Private room with AC\n• 3 meals provided daily\n• Weekly day off\n• NT$ 2,000 travel allowance/month\n• Year-end bonus	t	published	low	2026-05-30 14:00:00	\N	2026-06-15 03:56:39	2026-06-15 03:56:39	\N	\N	\N	Unknown	t	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	\N	\N	\N	f	\N
3	3	Construction Worker	Taiwan Build Co.	company	Taichung	NT$ 1,500 - 2,000	Day	["Weekly Pay"]	Construction	Taiwan Build Co. is hiring construction workers for a large residential development project in Taichung. Weekly pay with opportunity for overtime.	• Assist with concrete pouring and formwork\n• Carry and distribute materials on site\n• Operate basic construction tools\n• Follow site safety regulations\n• Clean and maintain work areas	• Physical fitness and stamina\n• Previous construction experience is a plus\n• Ability to work outdoors in various weather\n• Safety awareness\n• Team player	• Weekly cash payment\n• Safety equipment provided\n• Overtime available\n• Transportation to/from site\n• Performance bonus for project completion	f	published	low	2026-05-27 10:00:00	\N	2026-06-15 03:56:39	2026-06-15 03:56:39	\N	\N	\N	Unknown	t	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	\N	\N	\N	f	\N
4	4	CNC Machine Operator	Precision Parts Ltd.	factory	Taoyuan	NT$ 32,000 - 38,000	Month	["Verified Factory","Training Provided"]	Manufacturing	Precision Parts Ltd. is seeking skilled CNC Machine Operators for our Taoyuan factory. Full training provided for the right candidates.	• Set up and operate CNC milling and turning machines\n• Read and interpret technical drawings\n• Perform tool changes and machine calibration\n• Inspect finished parts with measuring instruments\n• Maintain machine cleanliness and report malfunctions	• Technical diploma or equivalent preferred\n• Basic understanding of metalworking\n• Ability to read simple technical drawings\n• Attention to detail and precision\n• Willingness to work overtime when needed	• Dormitory available (NT$ 2,500/month deduction)\n• Skill-based pay increases\n• Health and accident insurance\n• Year-end and Mid-Autumn bonuses\n• Free Mandarin language classes	f	published	low	2026-05-25 08:30:00	\N	2026-06-15 03:56:39	2026-06-15 03:56:39	\N	\N	\N	Unknown	t	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	\N	\N	\N	f	\N
5	3	Warehouse Packer	LogiTech Fulfillment	company	New Taipei City	NT$ 27,000 - 30,000	Month	["Night Shift Available","Immediate Start"]	Logistics	LogiTech Fulfillment is looking for warehouse packers for our e-commerce distribution center. Fast-paced environment with immediate start.	• Pick, pack, and label orders accurately\n• Scan barcodes and update inventory system\n• Organize and maintain warehouse sections\n• Load and unload delivery trucks\n• Meet daily packing targets	• Ability to stand for extended periods\n• Basic smartphone/scanner operation\n• Attention to detail for order accuracy\n• Ability to lift up to 20kg\n• Flexible schedule (day/night shifts)	• Night shift premium +NT$ 3,000\n• Free shuttle bus from MRT station\n• Meal allowance NT$ 100/day\n• Monthly attendance bonus\n• Group insurance	f	published	low	2026-05-29 11:00:00	\N	2026-06-15 03:56:39	2026-06-15 03:56:39	\N	\N	\N	Unknown	t	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	\N	\N	\N	f	\N
6	5	Elderly Care Assistant	Lin Family	family_care	Kaohsiung	NT$ 26,000 + Bonus	Month	["Live-in","Mandarin Required"]	Domestic Care	Kind and patient caregiver needed for 78-year-old grandmother in Kaohsiung. Live-in position with good working conditions.	• Daily care assistance (hygiene, mobility)\n• Prepare nutritious meals\n• Light physiotherapy exercises\n• Accompany on walks and outings\n• Keep living areas clean and organized	• Caregiving certification preferred\n• Conversational Mandarin required\n• Patience and empathy\n• Non-smoker\n• References from previous employer	• Private room and bathroom\n• All meals included\n• Monthly phone allowance\n• Quarterly performance bonus\n• Annual return ticket assistance	f	published	low	2026-05-26 16:00:00	\N	2026-06-15 03:56:39	2026-06-15 03:56:39	\N	\N	\N	Unknown	t	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	\N	\N	\N	f	\N
7	16	EDITED => test job agency company	gsjs	agency	Hsinchu	321321321	Month	\N	Construction	aad	aad	adasdad	feeee	t	submitted_for_review	low	2026-06-15 09:20:02	\N	2026-06-15 08:38:52	2026-06-15 09:20:02	08:00-17:00	adada	adad	Foreign Worker	t	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	\N	\N	\N	f	\N
8	17	Testing 1	Touyan, STD 12	company	New Taipei City	35000	Month	\N	Construction	Desciption	Desciption	Testing	testing	f	published	low	2026-06-15 13:56:53	\N	2026-06-15 13:56:53	2026-06-15 13:56:53	08:00 - 09:00	Mandarin	Testing	Foreign Worker	t	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	\N	\N	\N	f	\N
9	17	Construction Worker	Taiwan Build Co.	company	Taichung	NT$ 1,500 - 2,000	Day	["Weekly Pay"]	Construction	Taiwan Build Co. is hiring construction workers for a large residential development project in Taichung. Weekly pay with opportunity for overtime.	• Assist with concrete pouring and formwork\n• Carry and distribute materials on site\n• Operate basic construction tools\n• Follow site safety regulations\n• Clean and maintain work areas	• Physical fitness and stamina\n• Previous construction experience is a plus\n• Ability to work outdoors in various weather\n• Safety awareness\n• Team player	• Weekly cash payment\n• Safety equipment provided\n• Overtime available\n• Transportation to/from site\n• Performance bonus for project completion	f	published	low	2026-05-27 10:00:00	\N	2026-06-16 11:39:42	2026-06-16 11:39:42	\N	\N	\N	Unknown	t	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	\N	\N	\N	f	\N
10	17	Warehouse Packer	LogiTech Fulfillment	company	New Taipei City	NT$ 27,000 - 30,000	Month	["Night Shift Available","Immediate Start"]	Logistics	LogiTech Fulfillment is looking for warehouse packers for our e-commerce distribution center. Fast-paced environment with immediate start.	• Pick, pack, and label orders accurately\n• Scan barcodes and update inventory system\n• Organize and maintain warehouse sections\n• Load and unload delivery trucks\n• Meet daily packing targets	• Ability to stand for extended periods\n• Basic smartphone/scanner operation\n• Attention to detail for order accuracy\n• Ability to lift up to 20kg\n• Flexible schedule (day/night shifts)	• Night shift premium +NT$ 3,000\n• Free shuttle bus from MRT station\n• Meal allowance NT$ 100/day\n• Monthly attendance bonus\n• Group insurance	f	published	low	2026-05-29 11:00:00	\N	2026-06-16 11:39:42	2026-06-16 11:39:42	\N	\N	\N	Unknown	t	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	\N	\N	\N	f	\N
11	30	JOB PABRIK	FACTORY	factory	Chiayi City	35000	Month	\N	Logistics	ahsj	ahsj	2 yehajnsk	nskkw	t	published	low	2026-06-18 02:04:21	\N	2026-06-18 02:03:37	2026-06-18 02:04:32	\N	mandarin	work permit	Foreign Worker	t	\N	\N	Full-time	08:00-17:00	10	3	doem	phone	f	\N	\N	\N	\N	\N	\N	f	\N
13	30	CNC Machine Operator	Precision Parts Ltd.	factory	Taoyuan	NT$ 32,000 - 38,000	Month	["Verified Factory","Training Provided"]	Manufacturing	Precision Parts Ltd. is seeking skilled CNC Machine Operators for our Taoyuan factory. Full training provided for the right candidates.	• Set up and operate CNC milling and turning machines\n• Read and interpret technical drawings\n• Perform tool changes and machine calibration\n• Inspect finished parts with measuring instruments\n• Maintain machine cleanliness and report malfunctions	• Technical diploma or equivalent preferred\n• Basic understanding of metalworking\n• Ability to read simple technical drawings\n• Attention to detail and precision\n• Willingness to work overtime when needed	• Dormitory available (NT$ 2,500/month deduction)\n• Skill-based pay increases\n• Health and accident insurance\n• Year-end and Mid-Autumn bonuses\n• Free Mandarin language classes	f	published	low	2026-05-25 08:30:00	\N	2026-06-18 07:49:58	2026-06-18 07:49:58	\N	\N	\N	Unknown	t	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	\N	\N	\N	f	\N
14	33	job company yopmail	asgehb	company	Changhua County	35000	Month	\N	Agriculture	bwbsab	bwbsab	ahwubsb	jsjsjs	f	published	low	2026-06-19 05:55:52	\N	2026-06-19 05:55:52	2026-06-19 05:55:52	\N	mandarin	arbc	WNI	t	\N	\N	Full-time	08:00-17:00	\N	\N	\N	12bebsb	f	\N	\N	\N	\N	\N	\N	f	\N
17	34	illegal illegal	vwhab	family_care	Hsinchu	3000	Month	\N	Fisheries	illegal illegal	illegal illegal	1. ktp id\n2. admin approve	1. kjsjs	f	submitted_for_review	medium	2026-06-22 14:51:43	\N	2026-06-22 14:51:43	2026-06-22 14:51:43	\N	mandarin	yii	Foreign Worker	t	\N	\N	Contract	08:00	\N	\N	ash	telephone	f	\N	\N	\N	["Monthly salary appears too low (< NT$10,000). Possible data entry error or exploitation risk.","Job description is too short (less than 50 characters)."]	["Working hours"]	2026-06-22 14:51:43	f	\N
15	30	test under um4	FACTORY	factory	Chiayi City	10	Month	\N	Agriculture	registration cost 1000 us dollar	registration cost 1000 us dollar	appliers must pay 5000 us dollara	shesn	f	submitted_for_review	medium	2026-06-22 08:31:57	\N	2026-06-22 08:31:57	2026-06-22 08:33:21	\N	mandarin, english	arc	WNI	t	\N	\N	Full-time	08:00-17:00	1	3 yeaes	2	09817372	f	\N	\N	\N	["Monthly salary appears too low (< NT$10,000). Possible data entry error or exploitation risk.","Job description is too short (less than 50 characters)."]	["Working hours"]	2026-06-22 08:33:21	f	\N
16	34	illegal	vwhab	family_care	Kaohsiung City	50ribu dollar	Month	\N	Construction	illegal	illegal	jdhdhsillegal	snsnjx	f	submitted_for_review	medium	2026-06-22 14:47:29	\N	2026-06-22 14:47:29	2026-06-22 14:47:29	\N	jsje	sdhs	Foreign Worker	t	\N	\N	Contract	jsjsj	\N	\N	sissi	dd	f	\N	\N	\N	["Monthly salary appears too low (< NT$10,000). Possible data entry error or exploitation risk.","Job description is too short (less than 50 characters)."]	["Working hours"]	2026-06-22 14:47:29	f	\N
12	30	Electronic Assembly Operator	TSMC	factory	Hsinchu	NT$ 35,000 - 45,000	Month	["Verified Factory","Dormitory Provided"]	Agriculture	• Operate and monitor automated assembly equipment\n• Perform quality inspections on assembled components\n• Follow clean room protocols and safety procedures\n• Document production data and report anomalies\n• Participate in continuous improvement activities	• Operate and monitor automated assembly equipment\n• Perform quality inspections on assembled components\n• Follow clean room protocols and safety procedures\n• Document production data and report anomalies\n• Participate in continuous improvement activities	• Minimum 1 year manufacturing experience\n• Ability to work rotating shifts (day/night)\n• Good eyesight and manual dexterity\n• Basic understanding of electronic components\n• Willingness to learn and follow SOPs	• Free dormitory accommodation\n• Meals provided (3 meals/day)\n• Health insurance coverage\n• Annual performance bonus\n• Overtime pay at 1.5x rate	f	submitted_for_review	low	2026-05-28 09:00:00	\N	2026-06-18 07:49:58	2026-06-24 13:25:37	\N	mandarin	jejejejn	WNI	t	\N	\N	Contract	08:00-17:00	1	3	heheh	jehej122	f	\N	\N	\N	[]	["Working hours"]	2026-06-24 13:25:37	f	\N
18	30	test m5	FACTORY	factory	Changhua County	1	Month	\N	Agriculture	jejejd	jejejd	employee must pay NT$200000 and give a passport to agency during first month period	hshswb	f	rejected	critical	2026-06-25 04:53:52	\N	2026-06-25 04:53:52	2026-06-25 04:54:02	\N	vh	gu	Foreign Worker	t	\N	Auto-rejected: Critical risk flags detected. AI Risk Analysis (Critical): The salary listed is extremely low (1 / Month), which raises concerns about fair compensation.; The requirement for the employee to pay NT$200,000 and surrender their passport to an agency is a significant red flag for potential exploitation and trafficking.; The job description and duties are vague and nonspecific, indicating a lack of transparency.; The contact method is unclear (gtc), which may hinder proper communication and verification. Monthly salary appears too low (< NT$10,000). Possible data entry error or exploitation risk.	Full-time	08:00 - 17:00	1	3	chvhc	gtc	f	\N	\N	\N	["AI Risk Analysis (Critical): The salary listed is extremely low (1 \\/ Month), which raises concerns about fair compensation.; The requirement for the employee to pay NT$200,000 and surrender their passport to an agency is a significant red flag for potential exploitation and trafficking.; The job description and duties are vague and nonspecific, indicating a lack of transparency.; The contact method is unclear (gtc), which may hinder proper communication and verification.","Monthly salary appears too low (< NT$10,000). Possible data entry error or exploitation risk.","Job description is too short (less than 50 characters)."]	["Working hours"]	2026-06-25 04:54:02	f	\N
\.


--
-- Data for Name: job_types; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.job_types (id, job_type_name, slug, description, created_at, updated_at) FROM stdin;
1	Caregiver	caregiver	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
2	Factory Worker	factory_worker	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
3	Domestic Helper	domestic_helper	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
4	Nurse	nurse	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
5	Construction Worker	construction_worker	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
6	Restaurant / F&B	restaurant_fb	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
8	Driver	driver	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
9	IT / Software	it_software	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
10	Engineer	engineer	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
11	Teacher / Tutor	teacher_tutor	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
14	Fishery	fishery	\N	2026-06-15 03:56:36	2026-06-15 03:56:36
16	Factory / Manufacturing	factory	Assembly line, production, quality control	2026-06-15 03:56:36	2026-06-15 03:56:36
17	Care & Nursing	care_nursing	Elderly care, home nursing, childcare	2026-06-15 03:56:36	2026-06-15 03:56:36
13	Agriculture & Fishery	agriculture	Farming, fishing, aquaculture	2026-06-15 03:56:36	2026-06-15 03:56:36
18	Construction	construction	Building, civil works, renovation	2026-06-15 03:56:36	2026-06-15 03:56:36
19	F&B / Restaurant	fnb	Cook, waiter, kitchen staff	2026-06-15 03:56:36	2026-06-15 03:56:36
7	Retail / Sales	retail_sales	Shop assistant, cashier, sales rep	2026-06-15 03:56:36	2026-06-15 03:56:36
20	IT / Tech	it_tech	Software, hardware, IT support	2026-06-15 03:56:36	2026-06-15 03:56:36
21	Education / Teaching	education	English teacher, tutor, curriculum	2026-06-15 03:56:36	2026-06-15 03:56:36
22	Office / Admin	office_admin	Data entry, secretary, customer service	2026-06-15 03:56:36	2026-06-15 03:56:36
23	Driver / Logistics	driver_logistics	Truck driver, delivery, warehouse	2026-06-15 03:56:36	2026-06-15 03:56:36
24	Hospitality / Hotel	hospitality	Hotel staff, housekeeping, concierge	2026-06-15 03:56:36	2026-06-15 03:56:36
12	Cleaning / Sanitation	cleaning	Office cleaning, facility maintenance	2026-06-15 03:56:36	2026-06-15 03:56:36
25	Healthcare / Medical	healthcare	Nurse, lab tech, pharmacist, hospital admin	2026-06-15 03:56:36	2026-06-15 03:56:36
26	Translation / Interpretation	translation	Bilingual, interpreter, document translation	2026-06-15 03:56:36	2026-06-15 03:56:36
27	Design / Creative	design_creative	Graphic design, video, photography	2026-06-15 03:56:36	2026-06-15 03:56:36
15	Other	other	Other job categories	2026-06-15 03:56:36	2026-06-15 03:56:36
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: languages; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.languages (id, language_code, language_name, created_at, updated_at) FROM stdin;
1	EN	English	2026-06-15 03:56:36	2026-06-15 03:56:36
3	TL	Tagalog	2026-06-15 03:56:36	2026-06-15 03:56:36
6	JA	Japanese	2026-06-15 03:56:36	2026-06-15 03:56:36
7	ZH	Mandarin Chinese (普通話)	2026-06-15 03:56:36	2026-06-15 03:56:36
9	TW	Taiwanese (台語)	2026-06-15 03:56:36	2026-06-15 03:56:36
2	ID	Bahasa Indonesia	2026-06-15 03:56:36	2026-06-15 03:56:36
5	VI	Vietnamese (Tiếng Việt)	2026-06-15 03:56:36	2026-06-15 03:56:36
4	TH	Thai (ภาษาไทย)	2026-06-15 03:56:36	2026-06-15 03:56:36
10	PH	Filipino (Tagalog)	2026-06-15 03:56:36	2026-06-15 03:56:36
8	MY	Burmese (မြန်မာဘာသာ)	2026-06-15 03:56:36	2026-06-15 03:56:36
11	KH	Khmer (ភាសាខ្មែរ)	2026-06-15 03:56:36	2026-06-15 03:56:36
12	JP	Japanese (日本語)	2026-06-15 03:56:36	2026-06-15 03:56:36
13	KR	Korean (한국어)	2026-06-15 03:56:36	2026-06-15 03:56:36
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_05_31_100001_add_profile_fields_to_users_table	1
5	2026_05_31_100002_create_categories_table	1
6	2026_05_31_100003_create_cities_table	1
7	2026_05_31_100004_create_job_listings_table	1
8	2026_05_31_100005_create_job_applications_table	1
9	2026_05_31_100006_create_chat_messages_table	1
10	2026_05_31_133631_create_personal_access_tokens_table	1
11	2026_06_04_000001_create_employer_documents_table	1
12	2026_06_04_000002_create_payments_and_subscriptions_tables	1
13	2026_06_04_000003_create_verification_codes_table	1
14	2026_06_04_000004_add_phone_verified_fields_to_users_table	1
15	2026_06_05_083543_add_worker_profile_fields_to_users_table	1
16	2026_06_06_070531_add_media_fields_to_chat_messages_table	1
17	2026_06_06_094650_create_skills_table	1
18	2026_06_06_094651_create_industries_table	1
19	2026_06_08_000000_add_fcm_token_to_users_table	1
20	2026_06_08_033825_add_verification_note_to_users_table	1
21	2026_06_09_200555_add_is_admin_to_users_table	1
22	2026_06_09_201758_add_worker_details_to_users_table	1
23	2026_06_09_202725_add_milestone4_fields_to_job_listings_table	1
24	2026_06_11_200001_create_worker_types_table	1
25	2026_06_11_200002_create_languages_table	1
26	2026_06_11_200003_create_worker_languages_table	1
27	2026_06_11_200004_create_job_types_table	1
28	2026_06_11_200005_create_worker_job_types_table	1
29	2026_06_11_200006_create_document_types_table	1
30	2026_06_11_200007_create_worker_documents_table	1
31	2026_06_11_200008_create_worker_document_requirements_table	1
32	2026_06_11_200009_create_employer_staff_table	1
33	2026_06_11_200010_create_application_status_history_table	1
34	2026_06_11_200011_create_verification_logs_table	1
35	2026_06_11_200012_add_badge_fields_to_users_table	1
36	2026_06_11_200013_update_job_applications_add_fields	1
37	2026_06_11_200014_add_missing_fields_to_job_listings	1
38	2026_06_12_081246_add_review_fields_to_employer_documents_table	1
39	2026_06_12_083147_add_agency_staff_to_role_in_users_table	1
40	2026_06_13_063358_add_sponsorship_status_to_users_table	1
41	2026_06_13_211500_add_rejection_reason_to_job_listings_table	1
42	2026_06_13_235005_add_detected_language_to_chat_messages_table	2
43	2026_06_16_110710_create_nationalities_table	3
44	2026_06_16_200000_sync_existing_selfies_and_cvs_to_checklist	4
45	2026_06_17_145251_add_missing_fields_to_job_listings_table	5
46	2026_06_17_153119_add_license_expiry_date_to_users_table	5
47	2026_06_17_153126_add_agency_docs_to_job_listings_table	5
48	2026_06_17_154532_add_expiry_date_to_worker_documents_table	5
49	2026_06_18_085158_make_message_nullable_in_chat_messages_table	6
50	2026_06_18_091323_add_translation_to_chat_messages_table	7
51	2026_06_21_050030_create_safety_checks_table	8
52	2026_06_21_060000_create_report_and_trust_tables	9
53	2026_06_22_000001_add_m5_screening_fields_to_job_listings	10
54	2026_06_22_013505_add_provider_columns_to_users_table	11
55	2026_06_22_023131_create_blocked_users_table	12
56	2026_06_22_023131_create_chat_conversations_table	12
57	2026_06_22_023320_add_cv_data_to_chat_messages_table	12
58	2026_06_22_031214_create_notifications_table	13
59	2026_06_22_033606_create_audit_logs_table	14
60	2026_06_22_044450_create_ad_packages_table	15
61	2026_06_22_044451_add_sponsored_to_job_listings_table	15
62	2026_06_22_044451_create_advertisements_table	15
63	2026_06_22_221840_add_notification_preferences_to_users_table	16
64	2026_06_23_000001_create_application_status_logs_table	16
65	2026_06_23_100000_add_job_application_to_chat_messages_table	16
66	2026_06_23_110000_create_translation_logs_table	16
67	2026_06_23_120000_extend_reports_table	16
68	2026_06_23_130000_add_suspension_to_users_table	16
\.


--
-- Data for Name: nationalities; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.nationalities (id, name, code, created_at, updated_at) FROM stdin;
1	Indonesia	ID	2026-06-16 11:39:39	2026-06-16 11:39:39
2	Philippines	PH	2026-06-16 11:39:39	2026-06-16 11:39:39
3	Vietnam	VI	2026-06-16 11:39:39	2026-06-16 11:39:39
4	Thailand	TH	2026-06-16 11:39:39	2026-06-16 11:39:39
5	Myanmar	MM	2026-06-16 11:39:39	2026-06-16 11:39:39
6	Cambodia	KH	2026-06-16 11:39:39	2026-06-16 11:39:39
7	India	IN	2026-06-16 11:39:39	2026-06-16 11:39:39
8	Other	OTHER	2026-06-16 11:39:39	2026-06-16 11:39:39
\.


--
-- Data for Name: notifications; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.notifications (id, user_id, type, title, body, data, read_at, created_at, updated_at) FROM stdin;
1	20	chat_message	Pesan Baru dari companya	red	{"sender_id":"30","message_id":"144"}	\N	2026-06-24 13:12:31	2026-06-24 13:12:31
2	32	application_status	Status Lamaran Diperbarui	Lamaran Anda untuk posisi JOB PABRIK telah masuk shortlist.	{"job_id":"11","application_id":"10","status":"shortlisted"}	\N	2026-06-24 13:23:44	2026-06-24 13:23:44
3	32	application_status	Status Lamaran Diperbarui	Lamaran Anda untuk posisi JOB PABRIK telah diterima.	{"job_id":"11","application_id":"10","status":"accepted"}	\N	2026-06-24 13:23:49	2026-06-24 13:23:49
4	32	application_status	Status Lamaran Diperbarui	Lamaran Anda untuk posisi JOB PABRIK telah masuk shortlist.	{"job_id":"11","application_id":"10","status":"shortlisted"}	\N	2026-06-25 06:20:16	2026-06-25 06:20:16
5	32	application_status	Status Lamaran Diperbarui	Lamaran Anda untuk posisi JOB PABRIK telah diterima.	{"job_id":"11","application_id":"10","status":"accepted"}	\N	2026-06-25 06:20:18	2026-06-25 06:20:18
6	32	application_status	Status Lamaran Diperbarui	Lamaran Anda untuk posisi JOB PABRIK telah diterima.	{"job_id":"11","application_id":"10","status":"accepted"}	\N	2026-06-25 06:20:24	2026-06-25 06:20:24
7	20	application_status	Status Lamaran Diperbarui	Lamaran Anda untuk posisi JOB PABRIK telah masuk shortlist.	{"job_id":"11","application_id":"9","status":"shortlisted"}	\N	2026-06-25 06:21:58	2026-06-25 06:21:58
8	20	application_status	Status Lamaran Diperbarui	Lamaran Anda untuk posisi JOB PABRIK telah masuk shortlist.	{"job_id":"11","application_id":"9","status":"shortlisted"}	\N	2026-06-25 06:22:10	2026-06-25 06:22:10
9	20	application_status	Status Lamaran Diperbarui	Lamaran Anda untuk posisi JOB PABRIK telah diterima.	{"job_id":"11","application_id":"9","status":"accepted"}	\N	2026-06-25 06:23:41	2026-06-25 06:23:41
10	32	application_status	Status Lamaran Diperbarui	Lamaran Anda untuk posisi JOB PABRIK telah masuk shortlist.	{"job_id":"11","application_id":"10","status":"shortlisted"}	\N	2026-06-25 06:25:26	2026-06-25 06:25:26
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: payments; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.payments (id, user_id, amount, payment_gateway, transaction_id, status, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) FROM stdin;
11	App\\Models\\User	16	mobile-app	82bfdbc4de564c83fac5ddea77aabd59df2f7ebe5e4cd9667bd010c65cf5d48b	["*"]	2026-06-15 09:20:03	\N	2026-06-15 09:19:47	2026-06-15 09:20:03
88	App\\Models\\User	31	mobile-app	b12bfe0603e63be2cb43da9622c18c5aead54cd9af340381a70fa8e49c48755c	["*"]	2026-06-22 09:38:18	\N	2026-06-22 09:37:01	2026-06-22 09:38:18
73	App\\Models\\User	1	mobile-app	7f84888c293968637b840d14b50cd280c6d2a4d7c57cb20b0d74ea0a9731206f	["*"]	2026-06-19 16:56:00	\N	2026-06-19 16:54:23	2026-06-19 16:56:00
29	App\\Models\\User	27	mobile-app	054c2d892222a0e40bf62fcca1f1bdb75aa55a71b9dc5c1715dc14e557ae266b	["*"]	2026-06-16 16:27:49	\N	2026-06-16 16:04:17	2026-06-16 16:27:49
93	App\\Models\\User	35	mobile-app	f926dd0bf811b7f7b54ea635a252a5973149dc427067a99890ecf3368b636df5	["*"]	2026-06-24 07:25:35	\N	2026-06-24 06:54:52	2026-06-24 07:25:35
74	App\\Models\\User	32	mobile-app	fffce6fd914b55119fbc8d5a9d63e560addf0e4d0908461f46a0ced88409c4cc	["*"]	2026-06-19 16:58:41	\N	2026-06-19 16:57:03	2026-06-19 16:58:41
10	App\\Models\\User	10	mobile-app	1008d4e2112035c48c73f178b392bfc95b5a2b10ec436fbfd3ba05789e5f4ef2	["*"]	2026-06-15 09:01:15	\N	2026-06-15 08:49:38	2026-06-15 09:01:15
96	App\\Models\\User	30	mobile-app	95910bbf88f26935352733961cfc2101ea531f4c01e05636c1d245d6afa10190	["*"]	2026-06-25 06:26:58	\N	2026-06-25 06:19:57	2026-06-25 06:26:58
\.


--
-- Data for Name: reports; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.reports (id, reporter_id, reported_id, job_id, chat_message_id, report_type, reason, description, evidence_url, status, admin_note, created_at, updated_at, severity, resolved_at) FROM stdin;
\.


--
-- Data for Name: safety_checks; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.safety_checks (id, user_id, source_type, source_id, input_text, image_url, risk_level, result_json, language, created_at, updated_at) FROM stdin;
1	31	job	11	\N	\N	high	{"risk_level":"high","risk_reasons":["Informasi yang sangat minim mengenai deskripsi pekerjaan dan tanggung jawab.","Tidak ada rincian yang jelas tentang tunjangan dan fasilitas yang ditawarkan.","Kontak hanya melalui telepon tanpa informasi lebih lanjut tentang perusahaan atau cara resmi untuk memverifikasi."],"missing_info":["Deskripsi lengkap tentang tugas dan tanggung jawab pekerjaan.","Rincian tentang tunjangan, akomodasi, dan makanan.","Informasi kontak resmi dari perusahaan.","Klarifikasi tentang izin kerja dan status hukum lainnya."],"suggested_questions":["Apa saja tugas spesifik yang akan dilakukan dalam pekerjaan ini?","Bagaimana dengan akomodasi dan makanan yang disediakan?","Apakah ada kontrak kerja yang jelas dan dapat diakses?","Dapatkah saya mendapatkan informasi lebih lanjut tentang perusahaan ini?"],"recommended_action":"Disarankan untuk mencari bantuan dari organisasi yang melindungi pekerja migran dan mempertimbangkan untuk melaporkan tawaran pekerjaan ini jika ada indikasi penipuan.","disclaimer":"This AI analysis is for informational purposes only and does not constitute legal advice. Always verify job details through official channels."}	Indonesian	2026-06-22 09:37:17	2026-06-22 09:37:17
2	31	job	11	\N	\N	high	{"risk_level":"high","risk_reasons":["Informasi yang sangat tidak jelas dalam deskripsi pekerjaan dan tugas yang diberikan ('ahsj').","Persyaratan dan manfaat yang tidak terdefinisi dengan baik ('2 yehajnsk' dan 'nskkw').","Kontak hanya melalui telepon tanpa informasi lebih lanjut tentang perusahaan atau tempat kerja."],"missing_info":["Detail yang jelas tentang tugas dan tanggung jawab pekerjaan.","Informasi lengkap mengenai persyaratan dan manfaat yang ditawarkan.","Nama lengkap dan informasi kontak perusahaan yang lebih jelas.","Klarifikasi tentang kondisi tempat tinggal dan makanan."],"suggested_questions":["Apa saja tugas dan tanggung jawab spesifik dalam pekerjaan ini?","Apa saja manfaat yang ditawarkan kepada pekerja?","Bagaimana proses untuk mendapatkan izin kerja dan apa saja dokumen yang diperlukan?"],"recommended_action":"Sangat disarankan untuk menghubungi organisasi yang melindungi pekerja migran atau lembaga pemerintah terkait untuk melaporkan tawaran pekerjaan ini dan mendapatkan bantuan lebih lanjut.","disclaimer":"This AI analysis is for informational purposes only and does not constitute legal advice. Always verify job details through official channels."}	Indonesian	2026-06-22 09:37:34	2026-06-22 09:37:34
3	20	job	11	\N	\N	high	{"risk_level":"high","risk_reasons":["The job description lacks clarity and contains nonsensical text ('ahsj', 'yehajnsk', 'nskkw'), raising concerns about the legitimacy of the listing.","The contact method is only a phone number, which may not provide a reliable way to verify the employer's legitimacy or address potential issues.","The details regarding the dormitory, meals, and deductions are unclear ('doem'), which could indicate hidden costs or poor living conditions."],"missing_info":["Detailed job description and duties.","Clear requirements for the position.","Information about benefits and working conditions.","Contact information for the employer beyond just a phone number.","Clarification on the employment period ('3' is ambiguous)."],"suggested_questions":["Can you provide a detailed job description and list of duties?","What specific benefits are included with this position?","What are the living conditions like in the provided dormitory?","Are there any fees or deductions from the salary that I should be aware of?","Can you provide a written contract or agreement?"],"recommended_action":"Due to the high risk associated with this job listing, it is advisable to file a report with local authorities or seek assistance from organizations that protect migrant workers. Verify the legitimacy of the employer and the job offer through official channels before proceeding.","disclaimer":"This AI analysis is for informational purposes only and does not constitute legal advice. Always verify job details through official channels."}	English	2026-06-22 14:56:38	2026-06-22 14:56:38
4	35	job	11	\N	\N	high	{"risk_level":"high","risk_reasons":["Informasi yang diberikan sangat minim dan tidak jelas, seperti deskripsi pekerjaan dan persyaratan yang tidak dapat dipahami.","Tidak ada informasi yang jelas mengenai akomodasi, makanan, dan pemotongan gaji yang disebutkan sebagai 'doem'.","Kontak hanya melalui telepon tanpa rincian lebih lanjut mengenai perusahaan atau cara resmi untuk memverifikasi tawaran kerja."],"missing_info":["Deskripsi pekerjaan yang jelas dan rinci.","Persyaratan yang dapat dipahami dan relevan.","Informasi lengkap mengenai akomodasi dan manfaat yang ditawarkan.","Nama dan alamat lengkap perusahaan untuk verifikasi."],"suggested_questions":["Apa saja tugas dan tanggung jawab yang jelas untuk posisi ini?","Bagaimana rincian mengenai akomodasi dan makanan yang disediakan?","Apakah ada kontrak kerja resmi yang dapat dilihat sebelum menerima tawaran ini?"],"recommended_action":"Sangat disarankan untuk melaporkan tawaran kerja ini kepada otoritas terkait dan mencari bantuan dari lembaga perlindungan pekerja migran sebelum melanjutkan.","disclaimer":"This AI analysis is for informational purposes only and does not constitute legal advice. Always verify job details through official channels."}	Indonesian	2026-06-24 06:56:40	2026-06-24 06:56:40
5	35	job	11	\N	\N	high	{"risk_level":"high","risk_reasons":["Informasi mengenai gaji dan tunjangan tidak jelas dan tampak tidak realistis untuk lokasi dan jenis pekerjaan.","Deskripsi pekerjaan dan tanggung jawab sangat minim dan tidak memberikan informasi yang cukup.","Kontak hanya melalui telepon tanpa informasi lebih lanjut tentang perusahaan atau cara resmi untuk menghubungi mereka.","Tidak ada informasi tentang akomodasi yang jelas, hanya tertulis 'doem' yang tidak dapat dipahami."],"missing_info":["Detail lengkap tentang perusahaan dan reputasinya.","Informasi yang jelas tentang akomodasi dan makanan.","Keterangan yang lebih rinci tentang tugas dan tanggung jawab pekerjaan.","Proses perekrutan dan dokumen yang diperlukan.","Informasi kontak resmi atau alamat perusahaan."],"suggested_questions":["Apa nama lengkap dan informasi kontak perusahaan?","Dapatkah Anda memberikan rincian lebih lanjut tentang tugas dan tanggung jawab pekerjaan?","Bagaimana proses perekrutan dan dokumen apa yang diperlukan untuk melamar?"],"recommended_action":"Sangat disarankan untuk melaporkan informasi ini kepada otoritas terkait atau organisasi yang melindungi pekerja migran. Juga, cari bantuan dari kedutaan atau konsulat Anda.","disclaimer":"This AI analysis is for informational purposes only and does not constitute legal advice. Always verify job details through official channels."}	Indonesian	2026-06-24 07:12:52	2026-06-24 07:12:52
6	35	job	2	\N	\N	high	{"risk_level":"high","risk_reasons":["Informasi tentang status hukum pekerjaan tidak jelas, yang dapat menimbulkan risiko bagi pekerja migran.","Tidak ada informasi tentang periode pekerjaan, yang dapat mengindikasikan ketidakpastian dalam kontrak kerja.","Kurangnya informasi tentang jumlah pekerja yang terlibat dapat menunjukkan potensi eksploitasi."],"missing_info":["Tipe pekerjaan (apakah kontrak, sementara, dll.)","Jam kerja yang jelas","Jumlah pekerja yang akan dipekerjakan","Periode pekerjaan","Metode kontak untuk pertanyaan lebih lanjut","Status hukum pekerjaan"],"suggested_questions":["Apa jenis kontrak kerja yang ditawarkan?","Berapa jam kerja per hari dan per minggu?","Siapa yang dapat dihubungi jika ada masalah atau pertanyaan lebih lanjut tentang pekerjaan ini?","Apa yang terjadi jika ada masalah dengan status hukum saya sebagai pekerja?","Apakah ada jaminan tentang keamanan dan kesejahteraan pekerja di tempat tinggal?"],"recommended_action":"Sangat disarankan untuk mencari bantuan dari organisasi yang mendukung pekerja migran dan melaporkan tawaran pekerjaan ini jika ada indikasi penipuan atau eksploitasi.","disclaimer":"This AI analysis is for informational purposes only and does not constitute legal advice. Always verify job details through official channels."}	Indonesian	2026-06-24 07:25:43	2026-06-24 07:25:43
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
4oH8FhFnzaD1lVOf8Pw6AUchx9aAOrDQRr5OeDjW	\N	45.148.10.200	l9tcpid/v1.1.0	YTozOntzOjY6Il90b2tlbiI7czo0MDoiMDl0WDhGQ0hsZW1Yek5EbWs2Um94SEJxTHdTc21xdXZ2Vld6REJ6MCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8xMzAuOTQuMzQuMjQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6WW9aU0xqR3dvZlZsRWxsdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782365311
wwHZ5yOsNRdw3pvI0tIXHm5NDNPfzkLyW0v01H3L	\N	85.217.149.65	Mozilla/5.0 (compatible; ModatScanner/1.2; +https://modat.io/)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiWFVNZHZOVmo2QWFjY0xjRWtMQVJMWVBDZGV2TWgxT2VyVTMzeFEydSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8xMzAuOTQuMzQuMjQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6RzQwVWQyUUZua2pZOHB4UyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782372162
2WA92NziBFEx4iWVWjQiedzce7vsxQoN9HYvLJLi	\N	45.79.207.181	Mozilla/5.0 zgrab/0.x	YTozOntzOjY6Il90b2tlbiI7czo0MDoiS1NhcUgxd0FQU3hUUGhxZEM3VVZ4bHU0bU1jVUZMSFpHWk12SUs0VyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8xMzAuOTQuMzQuMjQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6WW9aU0xqR3dvZlZsRWxsdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782365820
XN5EGQQlFk3jDwa1mN07JdeUaxMetshTxMsfrulR	\N	85.217.149.65	Mozilla/5.0 (compatible; ModatScanner/1.2; +https://modat.io/)	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQlBHSXZZNXJyQ1VyRWNwMlhmUHBLSWpTbVpEeXd6d3JDT01OQ2tqRyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNToiaHR0cDovLzEzMC45NC4zNC4yNC9hZG1pbiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI1OiJodHRwOi8vMTMwLjk0LjM0LjI0L2FkbWluIjtzOjU6InJvdXRlIjtzOjE1OiJhZG1pbi5kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1782372162
RindpmVdyFFylZf58KdFTb8heJReHYW8Xh3TGzi3	\N	172.239.71.244	Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoidnVPaGp4aVdLM1VPcHl6cFdXbmtTcjEwVDVvSGtaWG5CQnlzeVBpeiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8xMzAuOTQuMzQuMjQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6WW9aU0xqR3dvZlZsRWxsdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782365836
OWSvkI1YFPWkjMr2WsjyLdDg4AIUJYLkc5U7PppE	\N	172.239.71.244	Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoidTdjZ21mb05qSW0xYWdhTnlRWUI4cHMxTHl5ZHI0aEd0SmxlOVZNcCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNToiaHR0cDovLzEzMC45NC4zNC4yNC9hZG1pbiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI1OiJodHRwOi8vMTMwLjk0LjM0LjI0L2FkbWluIjtzOjU6InJvdXRlIjtzOjE1OiJhZG1pbi5kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1782365836
5laJNVwdN1yoG1WURPMnSI9YFMiW1ITvD1wfbjHb	\N	122.37.71.24		YTozOntzOjY6Il90b2tlbiI7czo0MDoiTjBNbTBsdVJnZXpydDRZY3FIWjNOZHZUb0VRV2ZMMVFnMXRhMFRDUiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8xMzAuOTQuMzQuMjQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6WW9aU0xqR3dvZlZsRWxsdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782366398
Zjux8pGEkJhWlUsT0EPd0By9jac85toXRUFjFZnb	\N	43.159.34.167	Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1	YTo0OntzOjY6Il90b2tlbiI7czo0MDoic002TnZHc0xjOHdvS3paTVFwS3liV3cyQ1BsMm1RajhEMVFFekRXaSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMzAuOTQuMzQuMjQvYWRtaW4vbG9naW4iO3M6NToicm91dGUiO3M6MTE6ImFkbWluLmxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNToiaHR0cDovLzEzMC45NC4zNC4yNC9hZG1pbiI7fX0=	1782369042
3800dkfHcnK0VsdOdy3D4p9OIz9eR7Sf7Sso2LpH	\N	45.33.109.8	Mozilla/5.0 zgrab/0.x	YTozOntzOjY6Il90b2tlbiI7czo0MDoiTE5qOXFMRUZQdjM2SWpOZWlROHRVZWRzdzBhV1IyaUtlUlFIaTE0OCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8xMzAuOTQuMzQuMjQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6RzQwVWQyUUZua2pZOHB4UyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782369414
SdBsBhI8GxtEIuQGvRQb2aJTiOcExuldjgqoTePP	\N	153.162.229.209		YTozOntzOjY6Il90b2tlbiI7czo0MDoidzdxbUliVXJYWEx0dTBxMkJ2QTI0MGhzMmRSYjd0c2drNXRJSXF1TyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8xMzAuOTQuMzQuMjQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6eFpiRnROSGU1a3c4Y1lESiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782362557
QYS6c55RuyWniTEB6dVm02rovbwM6j1W1yi1mvXc	\N	66.228.53.46	Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiMnNTMXBXVzRkaVA0emQ2bk80MnRzTmJScERseHU1S0laUHdlcHB4WSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8xMzAuOTQuMzQuMjQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6RzQwVWQyUUZua2pZOHB4UyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782369434
N2ELym0hLZSAnHdzpEkqvTZK7TOmeGvSTYFyga94	\N	69.164.217.74	Mozilla/5.0 zgrab/0.x	YTozOntzOjY6Il90b2tlbiI7czo0MDoiMVZ6cmVoYWM3MVcxMEpZR1Z4R0Z0Z2MxNXlZblQ2aHRlMXJPV0k4NyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8xMzAuOTQuMzQuMjQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6eFpiRnROSGU1a3c4Y1lESiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782362725
5KXTX1JkBBr3HXnr92x4FX84mhqWBcY1zEVRDCPo	\N	66.228.53.46	Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRTlsQW1MTFFaV2JTRXlBSENHckQ0dUUyU2VpeGJDSHZldUVNOU90NiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNToiaHR0cDovLzEzMC45NC4zNC4yNC9hZG1pbiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI1OiJodHRwOi8vMTMwLjk0LjM0LjI0L2FkbWluIjtzOjU6InJvdXRlIjtzOjE1OiJhZG1pbi5kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1782369434
BEX8yR7DIwI2hXKWifRp4i0CeLjAuWu7cQvlSUJv	\N	31.132.90.3	libredtail-http	YTozOntzOjY6Il90b2tlbiI7czo0MDoiRzBTWDFzNDR2WDBmdHRqeU45T2JBbklnT2lLMElTTFY2U29WQlQ5MSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTQ1OiJodHRwOi8vMTMwLjk0LjM0LjI0L2luZGV4LnBocD9mdW5jdGlvbj1jYWxsX3VzZXJfZnVuY19hcnJheSZzPSUyRmluZGV4JTJGJTVDdGhpbmslNUNhcHAlMkZpbnZva2VmdW5jdGlvbiZ2YXJzJTVCMCU1RD1tZDUmdmFycyU1QjElNUQlNUIwJTVEPUhlbGxvIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OllvWlNMakd3b2ZWbEVsbHQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1782363236
qWzJJkGSBXsO5QuKyFe6TtlugXJkp5DSo2R4k30S	\N	20.163.32.168	Mozilla/5.0 zgrab/0.x	YTozOntzOjY6Il90b2tlbiI7czo0MDoiM1FVY0JLbTh0OU9JUUI0bE5XVEJ2SjVwTDY5WHFmNEMyeEkybDJCaiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8xMzAuOTQuMzQuMjQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6RzQwVWQyUUZua2pZOHB4UyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782370019
C91pQypLhZ7D985fvSyxrjfVaqi0302n7pBBbBGx	\N	31.132.90.3	libredtail-http	YTozOntzOjY6Il90b2tlbiI7czo0MDoiTEM1cTRZT3ZkcTNEU2I2SFBGWFQwcDB6ZWFjZlluSXJHUFNMNGtkRyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTQ1OiJodHRwOi8vMTMwLjk0LjM0LjI0L2luZGV4LnBocD9mdW5jdGlvbj1jYWxsX3VzZXJfZnVuY19hcnJheSZzPSUyRmluZGV4JTJGJTVDdGhpbmslNUNhcHAlMkZpbnZva2VmdW5jdGlvbiZ2YXJzJTVCMCU1RD1tZDUmdmFycyU1QjElNUQlNUIwJTVEPUhlbGxvIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6OllvWlNMakd3b2ZWbEVsbHQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1782363237
ut4CKyYtc3Q3MNilOsASq90HmP8L2YxmU4Dwz09t	\N	162.243.210.112	Mozilla/5.0 (Macintosh; Intel Mac OS X 11) AppleWebKit/538.41 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoidmtDNGdvSG9nS2xKbzRlYXBZNWQ3R3dvaHkzQk5qaWo4dzg2Qk5JOCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8xMzAuOTQuMzQuMjQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6RzQwVWQyUUZua2pZOHB4UyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782371147
MBamUWP9zhKD96i9rC2NOqkFx2yKWSDek2hNIeMD	\N	31.132.90.3	libredtail-http	YTozOntzOjY6Il90b2tlbiI7czo0MDoicHF1SkxoRjNaRnozVVk2TlVjVjB6ZndkUGJyWE50WHhxZThXaVVzZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk4OiJodHRwOi8vMTMwLjk0LjM0LjI0L2luZGV4LnBocD8lMkYlM0MlM0ZlY2hvJTI4bWQ1JTI4JTIyaGklMjIlMjklMjklM0IlM0YlM0UlMjAlMkZ0bXAlMkZpbmRleDEucGhwPSZjb25maWctY3JlYXRlJTIwJTJGPSZsYW5nPS4uJTJGLi4lMkYuLiUyRi4uJTJGLi4lMkYuLiUyRi4uJTJGLi4lMkZ1c3IlMkZsb2NhbCUyRmxpYiUyRnBocCUyRnBlYXJjbWQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6WW9aU0xqR3dvZlZsRWxsdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782363237
IMGlSh5Fsrj3uQcuO7OFDfcrJqoUW5FC0JiDa76y	\N	162.243.210.112	Mozilla/5.0 (Macintosh; Intel Mac OS X 11) AppleWebKit/538.41 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUEo4a3dRUkNIRWI0RHpzMWNTUzU2ampWSFhzWVh4OGRpRXh1SnFvWiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNToiaHR0cDovLzEzMC45NC4zNC4yNC9hZG1pbiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI1OiJodHRwOi8vMTMwLjk0LjM0LjI0L2FkbWluIjtzOjU6InJvdXRlIjtzOjE1OiJhZG1pbi5kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1782371148
umPiMoyfGdAjfz9odH4FFSRfNGYMXBX11hBLd469	\N	31.132.90.3	libredtail-http	YTozOntzOjY6Il90b2tlbiI7czo0MDoiWHRDTDR1bUZEa0dwTHdtQ2lQUDFPWjVQRHJ5ZkxuVlF0d0k5ZkFXMiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6ODc6Imh0dHA6Ly8xMzAuOTQuMzQuMjQvaW5kZXgucGhwP2xhbmc9Li4lMkYuLiUyRi4uJTJGLi4lMkYuLiUyRi4uJTJGLi4lMkYuLiUyRnRtcCUyRmluZGV4MSI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjpZb1pTTGpHd29mVmxFbGx0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1782363238
d4ycAN0vf51wTLIGqpm5zi1MnlNhG83rf0oUmdXg	\N	162.243.210.112	Mozilla/5.0 (Macintosh; Intel Mac OS X 11) AppleWebKit/538.41 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiUmFtZzlNVGJVbjVlVTFwTVdEVEg3QnM1M0NBdUpvQlhINHIzaTdabCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMzAuOTQuMzQuMjQvYWRtaW4vbG9naW4iO3M6NToicm91dGUiO3M6MTE6ImFkbWluLmxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1782371149
sAYB1i4ElPEBi1D4CxMcAW2U3sSyBWqYB8J0I2Zc	\N	85.217.149.20	Mozilla/5.0 (compatible; ModatScanner/1.2; +https://modat.io/)	YTozOntzOjY6Il90b2tlbiI7czo0MDoieFg3T21EcG5oQXFiRksza0tzV2lTY1l6RmhZNkRoVXhrUXkzZUtJSiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8xMzAuOTQuMzQuMjQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6RzQwVWQyUUZua2pZOHB4UyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782371263
5WU6cGuSzDlvXBChpELKpS0dqGlsnWUK5l6b8Ot5	1	203.173.91.121	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36	YTo1OntzOjY6Il90b2tlbiI7czo0MDoidVQ3UjE5TUdRTVMwS2dpNE9mN2xScGhmd0t6VWp5T3Y4bVNKRFJXeCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMwOiJodHRwOi8vMTMwLjk0LjM0LjI0L2FkbWluL2pvYnMiO3M6NToicm91dGUiO3M6MTY6ImFkbWluLmpvYnMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=	1782363296
MN2zUxwnNbgOM1vRHLtOtdO8yj3V3cMkKaHdF1nX	\N	85.217.149.20	Mozilla/5.0 (compatible; ModatScanner/1.2; +https://modat.io/)	YTo0OntzOjY6Il90b2tlbiI7czo0MDoienhhdjQ0cVlITk8yRGZhWEhZWkJQdGRvYXE0SnpNcjg2WEVvcHRYbyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNToiaHR0cDovLzEzMC45NC4zNC4yNC9hZG1pbiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI1OiJodHRwOi8vMTMwLjk0LjM0LjI0L2FkbWluIjtzOjU6InJvdXRlIjtzOjE1OiJhZG1pbi5kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1782371263
va6gT0g5SERMPMEWeB3wfoKPnthcNYWVlk6s6LcO	\N	172.104.215.178	Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoidVA2YWZubkpzdlRNRnE5eVJxZER3dmRJdW9mdkE4SGF4QXlmcVNFRCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8xMzAuOTQuMzQuMjQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6WW9aU0xqR3dvZlZsRWxsdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782363359
UfvhhY4An5R9Thpl1fHTxWgrQkTrO4nWbf8rlMOt	\N	212.127.90.201	libredtail-http	YTozOntzOjY6Il90b2tlbiI7czo0MDoiOWNwQ0NOTHBEaVI2dHZCbk11cVFUQ0xidlBWNnJRNjVob3N0bUlrYSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTQ1OiJodHRwOi8vMTMwLjk0LjM0LjI0L2luZGV4LnBocD9mdW5jdGlvbj1jYWxsX3VzZXJfZnVuY19hcnJheSZzPSUyRmluZGV4JTJGJTVDdGhpbmslNUNhcHAlMkZpbnZva2VmdW5jdGlvbiZ2YXJzJTVCMCU1RD1tZDUmdmFycyU1QjElNUQlNUIwJTVEPUhlbGxvIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6Okc0MFVkMlFGbmtqWThweFMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1782371684
kjsrRxzrL74tnOKgRmMSewfJUz6if3GQ2b8S33g4	\N	172.104.215.178	Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYlI4RWV2VlRqM0RkbHF1Ykdtd1YyQldob0NWTjJlcW8yamdQRHhCVyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNToiaHR0cDovLzEzMC45NC4zNC4yNC9hZG1pbiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI1OiJodHRwOi8vMTMwLjk0LjM0LjI0L2FkbWluIjtzOjU6InJvdXRlIjtzOjE1OiJhZG1pbi5kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1782363359
bFv9Rvc3PR1oKxVm0nbC5G6hqjJFIdgPrFflM1oK	\N	212.127.90.201	libredtail-http	YTozOntzOjY6Il90b2tlbiI7czo0MDoiSXBxTWpNUExneUhEaEE1dU5ydkJISnFCcm1uc2o5WGlQVlp1ZUxMSCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTQ1OiJodHRwOi8vMTMwLjk0LjM0LjI0L2luZGV4LnBocD9mdW5jdGlvbj1jYWxsX3VzZXJfZnVuY19hcnJheSZzPSUyRmluZGV4JTJGJTVDdGhpbmslNUNhcHAlMkZpbnZva2VmdW5jdGlvbiZ2YXJzJTVCMCU1RD1tZDUmdmFycyU1QjElNUQlNUIwJTVEPUhlbGxvIjtzOjU6InJvdXRlIjtzOjI3OiJnZW5lcmF0ZWQ6Okc0MFVkMlFGbmtqWThweFMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1782371685
8xJoDEDk61Jdxj3gbIWQsm1mhaTQbmwepk9l49fF	\N	176.65.139.225		YTozOntzOjY6Il90b2tlbiI7czo0MDoiS0N6bHFDRUFwbDlRTHQ3MDhsMHdPMnlOVnBuZzRuSlhBamJpS2I1NiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8xMzAuOTQuMzQuMjQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6WW9aU0xqR3dvZlZsRWxsdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782363682
iPkqPt5a2kGiArp2UJHhesJx3D1MxTZG6VYz93Bw	\N	212.127.90.201	libredtail-http	YTozOntzOjY6Il90b2tlbiI7czo0MDoiWkZKWndYbWNTUXdPY0FraEJLRmxtTm9raGY2QWFnV1RURXZCeXNpMCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk4OiJodHRwOi8vMTMwLjk0LjM0LjI0L2luZGV4LnBocD8lMkYlM0MlM0ZlY2hvJTI4bWQ1JTI4JTIyaGklMjIlMjklMjklM0IlM0YlM0UlMjAlMkZ0bXAlMkZpbmRleDEucGhwPSZjb25maWctY3JlYXRlJTIwJTJGPSZsYW5nPS4uJTJGLi4lMkYuLiUyRi4uJTJGLi4lMkYuLiUyRi4uJTJGLi4lMkZ1c3IlMkZsb2NhbCUyRmxpYiUyRnBocCUyRnBlYXJjbWQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6RzQwVWQyUUZua2pZOHB4UyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782371686
vYDQiB3Vm7nK1fFAWc1Y3fGeJTC0yuGerZWXFvTz	\N	195.154.169.173	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoib1ozM0M2UGMwQ0hEWldyMk9Qc0lBOW44b2NaZXhWRXZMbklVVVhaTSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8xMzAuOTQuMzQuMjQiO3M6NToicm91dGUiO3M6Mjc6ImdlbmVyYXRlZDo6WW9aU0xqR3dvZlZsRWxsdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1782364436
VheaKfaUkdzZ2TViDtvAJcEzjGCtYxNYHVZtC4Uv	\N	212.127.90.201	libredtail-http	YTozOntzOjY6Il90b2tlbiI7czo0MDoiMGNUNmc2TUxqQ0Q5SEdneWFnYWhkRXk4TTZFZDF5dnRjZm12bVN2YyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6ODc6Imh0dHA6Ly8xMzAuOTQuMzQuMjQvaW5kZXgucGhwP2xhbmc9Li4lMkYuLiUyRi4uJTJGLi4lMkYuLiUyRi4uJTJGLi4lMkYuLiUyRnRtcCUyRmluZGV4MSI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjpHNDBVZDJRRm5ralk4cHhTIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1782371686
\.


--
-- Data for Name: skills; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.skills (id, name, slug, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: subscriptions; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.subscriptions (id, user_id, plan_type, chat_translation_quota, starts_at, expires_at, status, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: translation_logs; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.translation_logs (id, chat_message_id, user_id, original_text, translated_text, source_language, target_language, trigger_type, created_at) FROM stdin;
1	144	30	red	merah	th	indonesian	auto	2026-06-24 13:12:31
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, full_name, role, nationality, current_city, company_name, industry, profile_completed, avatar_url, phone, license_number, verification_status, cv_url, preferred_language, phone_verified_at, date_of_birth, gender, address, educations, work_experiences, skills, fcm_token, verification_note, is_admin, worker_type, current_work_status, language_abilities, is_cv_public, onboarding_step, selfie_file_url, selfie_verified_at, verified_badge_status, verified_badge_updated_at, ready_to_work_status, ready_to_work_updated_at, sponsorship_required, employer_self_check_required, available_date, expected_salary, worker_type_id, unified_business_number, sponsorship_status, license_expiry_date, trust_score, violation_count, provider_name, provider_id, notification_preferences, is_suspended, suspension_reason, suspended_at) FROM stdin;
5	Lisa Wang	care@2ne5.tw	\N	$2y$12$lZb4WNf2IwbqfuPEA2oA5uTFS6w827pWf3ezUOqgFA8lAVMhUmYji	\N	2026-06-15 03:56:39	2026-06-25 06:19:26	Lisa Wang	family_care	\N	\N	Wang Family	Domestic Care	t	\N	\N	\N	manually_verified	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	t	1	\N	\N	unverified	\N	not_ready	\N	f	f	\N	\N	\N	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
9	Somchai Prasert	somchai.p@email.com	\N	$2y$12$3Sld6E1at2yGdcXosIIhdum1wWO.fgTQGPsYfw3YjPf3oNYvInKIu	\N	2026-06-15 03:56:39	2026-06-25 06:19:26	Somchai Prasert	worker	Thailand	Taichung	\N	\N	t	\N	\N	\N	unverified	https://example.com/cvs/somchai_prasert.pdf	Thai	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	t	1	\N	\N	unverified	\N	not_ready	\N	f	f	\N	\N	\N	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
8	Siti Rahayu	siti.rahayu@email.com	\N	$2y$12$SPvqpezbRAEgY0zppj6GLeBfFZuZ37IcfWL1dgh3zzR37UmKItB8.	\N	2026-06-15 03:56:39	2026-06-25 06:19:26	Siti Rahayu	worker	Indonesia	Kaohsiung	\N	\N	t	\N	\N	\N	unverified	https://example.com/cvs/siti_rahayu.pdf	Indonesian	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	t	1	\N	\N	unverified	\N	not_ready	\N	f	f	\N	\N	\N	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
11	artablue collar	artabluecollar@yopmail.com	2026-06-15 04:41:42	$2y$12$BcLQIbaim8C8axGQO/qAb.flU.1swbIuAtPETSyuq5pP/SKpM/242	\N	2026-06-15 04:41:42	2026-06-15 04:43:12	artablue collar	worker	\N	\N	\N	\N	f	\N	\N	\N	unverified	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	blue_collar	\N	\N	t	6	\N	\N	verified	\N	ready	2026-06-15 04:43:12	f	f	\N	\N	2	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
6	Robert Huang	agency@2ne5.tw	\N	$2y$12$mAXkrrpL2gIWFrjlthigd.CQmBzGhftwwbE4.ckTqW9XM9K0lt8gS	\N	2026-06-15 03:56:39	2026-06-25 06:19:26	Robert Huang	agency	\N	\N	Global Staffing Agency	Recruitment	t	\N	\N	LIC-TW-889977	manually_verified	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	t	1	\N	\N	unverified	\N	not_ready	\N	f	f	\N	\N	\N	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
10	arta student	artastudent1@yopmail.com	2026-06-15 04:00:56	$2y$12$0kyqD.Wo6vHPoldYUPvZkeSwwZmfFZmA5sRnv0Wjb5Zs1b9t1NKhm	\N	2026-06-15 04:00:56	2026-06-15 09:01:15	arta student	worker	assj	sz	\N	\N	t	\N	081256985457	\N	unverified	\N	\N	\N	2000-01-01	Male	123 mainnn	[]	[]	[]	cDN4lpVxQhCL2da20x59tf:APA91bGI0WCX8M0VUidq0_f1eUoSQD-HByvlMxLdaiv5Znii65m9T4r17wCgzHJrnE66M9LJZ-dTN_2yQJl5U5yZP9ZEG1o9P4N63nLQ8tzqLi2Ax-70dCc	\N	f	student	Available	["Mandarin - Basic","Indonesia - Basic"]	t	6	\N	\N	verified	\N	ready	2026-06-15 04:01:37	f	f	2026-06-15	131616.00	1	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
12	Rta wh	artawhitecollar2@yopmail.com	2026-06-15 04:52:52	$2y$12$w8FDe.z4B6UjN2MkP3KYaONT/HAUNFELAHjD1ThMnDYAs1z0QXLxe	\N	2026-06-15 04:52:52	2026-06-15 07:52:52	Rta wh	worker	naa	aa	\N	\N	t	\N	\N	\N	unverified	\N	\N	\N	\N	\N	\N	\N	\N	\N	cDN4lpVxQhCL2da20x59tf:APA91bGI0WCX8M0VUidq0_f1eUoSQD-HByvlMxLdaiv5Znii65m9T4r17wCgzHJrnE66M9LJZ-dTN_2yQJl5U5yZP9ZEG1o9P4N63nLQ8tzqLi2Ax-70dCc	\N	f	white_collar	Available	\N	t	6	\N	\N	verified	\N	pending	\N	t	f	2026-06-15	516191.00	3	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
13	cincong	artataiwan@yopmail.com	2026-06-15 08:09:40	$2y$12$29VrOYNtxlxU4eDK2hfYSui7CJAUvAi0aUOM1yYjlfE7ibvr54JFW	\N	2026-06-15 08:09:40	2026-06-15 08:09:49	cincong	worker	yb	gugu	\N	\N	t	\N	\N	\N	unverified	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	taiwanese	\N	\N	t	6	\N	\N	verified	\N	pending	2026-06-15 08:09:40	f	f	2026-06-15	68686.00	6	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
2	Maria Santos	worker@2ne5.tw	\N	$2y$12$1zqugZb1PsKXWm4b2x9zGu.p/VbgZcskSS/y8NYng0oU9fqAdYbuG	\N	2026-06-15 03:56:39	2026-06-25 06:19:26	Maria Santos	worker	Philippines	Taoyuan City	\N	\N	t	\N	\N	\N	unverified	https://example.com/cvs/maria_santos.pdf	Tagalog	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	t	1	\N	\N	unverified	\N	not_ready	\N	f	f	\N	\N	\N	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
14	arta other	artaother@yopmail.com	2026-06-15 08:11:48	$2y$12$ZLQN2/qIggMRLjsRfTDx3eC6yLufQg5BJYYbWfVE4LNRrQqdvYtyG	\N	2026-06-15 08:11:49	2026-06-15 08:11:54	arta other	worker	hshs	dd	\N	\N	t	\N	\N	\N	unverified	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	other	\N	\N	t	6	\N	\N	verified	\N	pending	\N	f	f	2026-06-15	594949.00	8	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
7	Nguyen Thi Lan	nguyen.lan@email.com	\N	$2y$12$e.Bz97uwO8UN/S39JIsdJe2Ic5fG4HS97bPp/cu.uxHd6.jO2kJfG	\N	2026-06-15 03:56:39	2026-06-25 06:19:26	Nguyen Thi Lan	worker	Vietnam	Taipei	\N	\N	t	\N	\N	\N	unverified	https://example.com/cvs/nguyen_lan.pdf	Vietnamese	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	t	1	\N	\N	unverified	\N	not_ready	\N	f	f	\N	\N	\N	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
15	arta agency	artaagencystaff@yopmail.com	2026-06-15 08:12:42	$2y$12$L38UkFDVOx2lkvO/E8Td9.K4PsNeCuBLrP2lbCY.m/IKVnXSFRVQG	\N	2026-06-15 08:12:43	2026-06-15 08:13:01	arta agency	agency_staff	\N	taipei	abchjw	\N	t	\N	085694523322	\N	pending	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	t	5	\N	\N	pending	\N	not_ready	\N	f	f	\N	\N	\N	12345678	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
4	James Lin	factory@2ne5.tw	\N	$2y$12$ZjmThQlcNqjQYAM.BmLsVub0fIUIiZ60iW2Y5.jMvNoXJLDMf520O	\N	2026-06-15 03:56:39	2026-06-25 06:19:26	James Lin	factory	\N	\N	Hsinchu Manufacturing	Manufacturing	t	\N	\N	\N	manually_verified	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	t	1	\N	\N	unverified	\N	not_ready	\N	f	f	\N	\N	\N	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
16	arta agency company	artaagencycompany@yopmail.com	2026-06-15 08:15:48	$2y$12$sCRJtEckiQMbBqdkg/r/EujHNlGh4IlAwVJieVHUYOBaiKrEiuBX2	\N	2026-06-15 08:15:48	2026-06-15 09:19:48	arta agency company	agency	\N	taipei	gsjs	it	t	\N	12345678	abc	basic_verified	\N	\N	\N	\N	\N	\N	[]	[]	[]	eSZynRA5QoifunaAoFkkRY:APA91bEs_BwDU0ghOYWAD-xDwj6RwoNRYk9ta_OmaDuCThPIb9qe4aXj-f3431OHCpl91-MgxnuVIt-X7okYob9xy8n0yz8sS3S1Te4wXlHihSodZE9er0g	\N	f	\N	\N	[]	t	5	\N	\N	verified	2026-06-15 08:32:17	not_ready	\N	f	f	\N	\N	\N	12345678	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
1	Super Admin	admin@2ne5.tw	2026-06-15 03:56:37	$2y$12$bi0b0d2Ek0yFteASopCMBOIDAxSN..Kdz22Ah9CcFCp2OnGFG7Vf.	\N	2026-06-15 03:56:37	2026-06-19 16:55:56	Super Admin 2ne5	worker	\N	\N	\N	\N	f	\N	\N	\N	unverified	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	t	4	\N	\N	unverified	\N	not_ready	\N	f	f	\N	\N	\N	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
3	David Chen	company@2ne5.tw	\N	$2y$12$ArO2fhwQoAMJrNLdZeOBQOHEI5jsFqkyzQMufIoQ8Fnkbu5.jWzte	\N	2026-06-15 03:56:39	2026-06-25 06:19:26	David Chen	company	\N	\N	Taiwan Tech Corp	Technology	t	\N	\N	\N	basic_verified	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	t	1	\N	\N	unverified	\N	not_ready	\N	f	f	\N	\N	\N	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
19	Nendan	testing3@gmail.com	2026-06-15 14:17:04	$2y$12$lc7TRbBEGMXVktbfXY3yau0aDJcp6YfnVRm5PKtBU9vRSoIFChGra	\N	2026-06-15 14:17:05	2026-06-16 00:54:19	Nendan	worker	Wus	jacki	\N	\N	t	\N	\N	\N	unverified	http://130.94.34.24/storage/cvs/cv_19_1781571259.pdf	\N	\N	\N	\N	\N	\N	\N	\N	f9p3ds7vTaSLkxd2e3lbQz:APA91bHDEJVob281KY_BlNeW_FC1VWHcpGr9Tdra9a8osVdowS-ExGxG7c5Rmz1i6R8dhGsKzYnyuhEAWX9cv0MkEUVWBSBYy_SNAdzNTNUjVIp6A6a6fH0	\N	f	white_collar	\N	\N	t	6	\N	\N	verified	2026-06-15 14:19:12	ready	2026-06-15 14:19:12	t	f	2026-06-15	30000.00	3	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
17	Worker Satu	worker1@gmail.com	2026-06-15 13:49:55	$2y$12$Qii9D9TFGqa6wPxsEOO58OgTSqNObxffTMGQHazHeh2SUEfVTF9e2	\N	2026-06-15 13:49:55	2026-06-16 00:56:14	Worker Satu	company	\N	Touyan	Touyan, STD 12	\N	t	\N	088888888888	\N	basic_verified	\N	\N	\N	\N	\N	\N	\N	\N	\N	f9p3ds7vTaSLkxd2e3lbQz:APA91bHDEJVob281KY_BlNeW_FC1VWHcpGr9Tdra9a8osVdowS-ExGxG7c5Rmz1i6R8dhGsKzYnyuhEAWX9cv0MkEUVWBSBYy_SNAdzNTNUjVIp6A6a6fH0	\N	f	\N	\N	\N	t	5	\N	\N	verified	2026-06-15 13:53:44	not_ready	\N	f	f	\N	\N	\N	87682341	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
18	Rikzy Nuansa	worker2@gmail.com	2026-06-15 13:58:32	$2y$12$KWxrK4tOALosyPS/kcpzK.UdenzzJX.Ybmg17zOrtXpttl3Ei2rMq	\N	2026-06-15 13:58:32	2026-06-16 01:21:50	Rikzy Nuansa	worker	info	yu	\N	\N	t	\N	08454848	\N	unverified	http://130.94.34.24/storage/cvs/cv_18_1781532846.pdf	\N	\N	2000-01-01	Male	JTTT	[]	[]	[]	f9p3ds7vTaSLkxd2e3lbQz:APA91bHDEJVob281KY_BlNeW_FC1VWHcpGr9Tdra9a8osVdowS-ExGxG7c5Rmz1i6R8dhGsKzYnyuhEAWX9cv0MkEUVWBSBYy_SNAdzNTNUjVIp6A6a6fH0	\N	f	student	\N	[]	t	5	\N	\N	verified	\N	not_ready	\N	f	f	\N	\N	1	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
21	student arta	studentarc@yopmail.com	2026-06-16 12:28:00	$2y$12$/VkWv4948TxxGCtLBypEfeGSF5SIu3qULcABywrxQLs5Od/4VdEl2	\N	2026-06-16 12:28:00	2026-06-16 12:45:22	student arta	worker	Cambodia	Chiayi County	\N	\N	t	\N	081358607741	\N	unverified	http://130.94.34.24/storage/cvs/cv_21_1781613894.pdf	English	\N	\N	Male	\N	[]	[]	[]	\N	\N	f	student	\N	[]	t	6	http://130.94.34.24/storage/selfies/selfie_21_1781612881.jpg	2026-06-16 12:30:20	verified	2026-06-16 12:30:30	ready	2026-06-16 12:30:36	f	f	2026-06-16	25000.00	1	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
26	Arta	agencycompany@yopmail.com	2026-06-16 14:38:07	$2y$12$24Xewqnsz70fcbIgk1Jbbevoi/hF5N.vL.PjhucR9vc5EpS8xXSWi	\N	2026-06-16 14:38:08	2026-06-16 14:39:12	Arta	agency	\N	Chiayi City	hshshs	\N	t	\N	0813586077774	\N	basic_verified	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	\N	\N	\N	t	5	http://130.94.34.24/storage/selfies/selfie_26_1781620688.jpg	\N	verified	2026-06-16 14:39:12	not_ready	\N	f	f	\N	\N	\N	12345679	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
24	arc other	arcother@yopmail.com	2026-06-16 14:28:04	$2y$12$IOoCirU5bzceZV6HqG3aeef86hqjGQt3Y0wRhIbgp76z3RwNRjD8a	\N	2026-06-16 14:28:04	2026-06-16 14:32:40	arc other	worker	Cambodia	Chiayi County	\N	\N	t	\N	\N	\N	unverified	\N	Mandarin Chinese (普通話)	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	arc_other	\N	\N	t	6	http://130.94.34.24/storage/selfies/selfie_24_1781620084.jpg	2026-06-16 14:32:33	verified	2026-06-16 14:32:40	ready	2026-06-16 14:32:40	f	f	2026-06-16	20567.00	4	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
22	blue collae	bluecollar@yopmail.com	2026-06-16 13:34:53	$2y$12$FSguZ4kXpgPocmzjkOPTlesJ.W4znGX6luMaVMNb0fctFXcuqWmN.	\N	2026-06-16 13:34:53	2026-06-16 14:11:09	blue collae	worker	Cambodia	Chiayi County	\N	\N	t	\N	\N	\N	unverified	\N	Tagalog	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	blue_collar	\N	\N	t	6	http://130.94.34.24/storage/selfies/selfie_22_1781616894.jpg	2026-06-16 14:11:09	verified	2026-06-16 14:11:09	ready	2026-06-16 14:11:09	f	f	2026-06-16	356000.00	2	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
28	Rizky Nuansa Nanda Permana	employer2@gmail.com	2026-06-16 16:06:28	$2y$12$nn7s/1GhKAZAeZXwiwvH2ebZPgkArQt3CXj0VjMlUM0mvVhSJK5dy	\N	2026-06-16 16:06:29	2026-06-16 16:34:41	Rizky Nuansa Nanda Permana	family_care	Indonesia	Chiayi County	Irasa	Fisheries	t	\N	80888088	\N	unverified	\N	\N	\N	\N	\N	\N	[]	[]	[]	dtvoMT10QWuZZzKiDhHhxQ:APA91bHnIbR2CBTVmLG7i8zRFW4sM9nIytHScSbL_FHPnJfJzybU2vMN-JxgX19-9E_eeqdIrG7jqpK3StRxxux_nBW5RtoRRWSBGng4xnqnb2wH5USrtvQ	\N	f	\N	\N	[]	t	5	http://130.94.34.24/storage/selfies/selfie_28_1781625989.jpg	\N	unverified	\N	not_ready	\N	f	f	\N	\N	\N	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
27	Employer Name	employer1@gmail.com	2026-06-16 15:30:33	$2y$12$faqmCeAylqeH4eiXnSDnOu7vP2mOGk4PmXJu6EvCuB5x8VdAacodK	\N	2026-06-16 15:30:34	2026-06-16 16:04:18	Employer Name	company	\N	Changhua County	ghh	\N	t	\N	08555885555555	\N	pending	\N	\N	\N	\N	\N	\N	\N	\N	\N	cp83hoVDRXOnf698n8gSwV:APA91bHt_lti6BPGzDfPDAih2pRQgrEKk4gnHNzZWbAPxK6uK7tT2Yhlz2tmbky6_q_5kvm73ru0p0etSZzgx2FP92IV0IZUSszSQGRKwnKdYYWo2JHkd0E	\N	f	\N	\N	\N	t	5	http://130.94.34.24/storage/selfies/selfie_27_1781623834.jpg	\N	pending	\N	ready	\N	f	f	\N	\N	\N	12345678	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
23	white collar	whitcollar@yopmail.com	2026-06-16 14:14:31	$2y$12$J3R4oToAk5ANqLCcKJE.eO1zYUf5egHNWv7O8Y27b0svm82F8flje	\N	2026-06-16 14:14:31	2026-06-16 14:15:26	white collar	worker	Cambodia	Changhua County	\N	\N	t	\N	\N	\N	unverified	\N	Mandarin Chinese (普通話)	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	white_collar	\N	\N	t	6	http://130.94.34.24/storage/selfies/selfie_23_1781619272.jpg	2026-06-16 14:15:12	verified	2026-06-16 14:15:15	ready	2026-06-16 14:15:26	t	f	2026-06-16	644664.00	3	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
29	blue c	bluecollar1@yopmail.com	2026-06-17 02:39:06	$2y$12$qigX/YkNix0AMLXQS7senelMVex4NYz10m93dUXCKxzP5WYYucJEm	\N	2026-06-17 02:39:06	2026-06-17 02:40:10	blue c	worker	Cambodia	Changhua County	\N	\N	t	\N	\N	\N	unverified	\N	English	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	blue_collar	\N	\N	t	6	http://130.94.34.24/storage/worker_personal_documents/personal_29_selfie_1781663960.jpg	\N	pending	\N	pending	\N	f	f	2026-06-17	3000.00	2	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
25	abc	aprc@yopmail.com	2026-06-16 14:34:27	$2y$12$yG9S0Y4BYxHeVodtbrlYnuWDRfxHK5EPb9b2rSrnefkDZUKcWonBa	\N	2026-06-16 14:34:28	2026-06-16 14:35:41	abc	worker	Cambodia	Chiayi City	\N	\N	t	\N	\N	\N	unverified	\N	Tagalog	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	aprc	\N	\N	t	6	http://130.94.34.24/storage/selfies/selfie_25_1781620468.jpg	2026-06-16 14:35:36	verified	2026-06-16 14:35:41	ready	2026-06-16 14:35:41	f	f	2026-06-16	515181.00	5	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
32	Andi Pratama	bluecollarworker@gmail.com	2026-06-19 02:57:29	$2y$12$YCqGKLwVQU52.a2fvOMK7OkVdeCDPT89aI19gegq1ylWaKJlC/b4i	\N	2026-06-19 02:57:29	2026-06-19 13:57:16	Andi Pratama	worker	Indonesia	Hsinchu County	\N	\N	t	\N	\N	\N	unverified	http://130.94.34.24/storage/cvs/cv_32_1781842505.pdf	Taiwanese (台語)	\N	\N	Male	\N	[]	[]	[]	\N	\N	f	blue_collar	\N	[]	t	6	http://130.94.34.24/storage/selfies/selfie_32_1781837850.jpg	2026-06-19 03:34:31	verified	2026-06-19 03:34:39	pending	\N	f	f	2026-06-19	21333.00	2	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
31	Rizky Nuansa Nanda Permana	workerstudent@gmail.com	2026-06-19 01:49:40	$2y$12$.iTu0FU88tp4ntK1qL6EReutxwoVXNSObLxuZaJ3GgV83RKuKXHd.	\N	2026-06-19 01:49:41	2026-06-22 09:37:01	Rizky Nuansa Nanda Permana	worker	Indonesia	Changhua County	\N	\N	t	\N	\N	\N	unverified	\N	English	\N	\N	\N	\N	\N	\N	\N	d6AznANyRgSInQY9mZ3Ezi:APA91bFupLbzSXW7sB_eboSPVCxSDZKtmNQV-ArKGa4otHFxaWFr24keWAk7X-YhTc64D1ZimZFn2VKyfhHDK03L6tQykmIBP7BY3-TjJKcAk5WEqcK1_qo	\N	f	student	\N	\N	t	6	http://130.94.34.24/storage/selfies/selfie_31_1781833781.jpg	2026-06-19 03:07:15	verified	2026-06-19 03:07:34	ready	2026-06-19 03:12:51	f	f	2026-06-19	30000.00	1	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
34	family employer	familyemployer@yopmail.com	2026-06-19 05:58:20	$2y$12$gnIiwtFE5j.jnuCIe5rIBu/5t9Yci4okz86kHpzltHLBRiNwpzVHm	\N	2026-06-19 05:58:20	2026-06-22 14:42:05	family employer	family_care	\N	Hsinchu County	vwhab	\N	t	\N	886562959	\N	basic_verified	\N	\N	\N	\N	\N	\N	\N	\N	\N	f2w_OAepTD-mBIE2nFRtxQ:APA91bHWgNNIhw0uMXUAl-jXDkOD0YR_fSngyAFDEb7u1egdch48GlADKyvKGbbZUJ1rF2r3ZKR_mkO0aw9oKoEI6C5B-sLkOy58UAUVG_kkFhCf0ws7CMg	\N	f	\N	\N	\N	t	5	http://130.94.34.24/storage/selfies/selfie_34_1781848701.jpg	\N	verified	2026-06-22 14:41:31	not_ready	\N	f	f	\N	\N	\N	12345678	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
33	arrrrr	company@yopmail.com	2026-06-19 05:54:17	$2y$12$wVXE2R1hfpeS.Xx33uCAAe2Yh/ELvSYhXh9FuQpy5Fz7XuIhLvspi	\N	2026-06-19 05:54:18	2026-06-22 14:33:23	arrrrr	company	Indonesia	Changhua County	asgehb	Domestic Care	t	\N	08659384625	\N	basic_verified	\N	\N	\N	\N	\N	\N	[]	[]	[]	f2w_OAepTD-mBIE2nFRtxQ:APA91bHWgNNIhw0uMXUAl-jXDkOD0YR_fSngyAFDEb7u1egdch48GlADKyvKGbbZUJ1rF2r3ZKR_mkO0aw9oKoEI6C5B-sLkOy58UAUVG_kkFhCf0ws7CMg	\N	f	\N	\N	[]	t	5	http://130.94.34.24/storage/selfies/selfie_33_1781848458.jpg	\N	verified	2026-06-19 05:54:59	not_ready	\N	f	f	\N	\N	\N	12345678	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
35	Egyu Ryul	worker1@testing.com	2026-06-24 06:54:52	$2y$12$oQlOr5TRK/yqtSM52NmpNOBqRMgOl6zYXV3NkkfpOpSaQtp.2SGG.	\N	2026-06-24 06:54:52	2026-06-24 06:55:50	Egyu Ryul	worker	Cambodia	Taoyuan	\N	\N	t	\N	\N	\N	unverified	\N	Tagalog	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	student	\N	\N	t	6	\N	\N	pending	\N	not_ready	\N	f	f	2026-06-24	21321.00	1	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
30	companya	factory@yopmail.com	2026-06-18 02:29:23	$2y$12$xZ9vylP.WQe/ED.B2iqRkec4D7vOnQb8UGvIcWBPaFS3opkW7ayFm	\N	2026-06-18 01:59:33	2026-06-24 13:02:34	companya	factory	Thailand	Chiayi City	FACTORY	Agriculture	t	http://130.94.34.24/storage/avatars/avatar_30_1781772425.jpg	0813586945254	\N	basic_verified	\N	\N	\N	\N	\N	\N	[]	[]	[]	eA-77u2jSfeivB78-mpIVu:APA91bGAofOmQ0Eq10VhIwk2L25wkA75aHGJyLSsmopU9Hp9BkknfUgTWfExy_9vFoqDZt8dOTB27Jc9ikXipHCPkv7kkfuX45LfgZoqJ0ePYHYZKJIYAdQ	\N	f	\N	\N	[]	t	5	http://130.94.34.24/storage/selfies/selfie_30_1781747974.jpg	\N	verified	2026-06-18 02:04:11	not_ready	\N	f	f	\N	\N	\N	12345678	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
20	student other	studentp2@yopmail.com	2026-06-16 10:58:01	$2y$12$9N3Rf/mYP7Wfgz.xKbRbhejugLzaUFzkCSHaNxY9ZTOPjYW3ebP/S	\N	2026-06-16 10:58:01	2026-06-25 06:09:17	student other	worker	Indonesia	Changhua County	\N	\N	t	http://130.94.34.24/storage/avatars/avatar_20_1781927725.jpg	0813586946161	\N	unverified	http://130.94.34.24/storage/cvs/cv_20_1781748363.pdf	indonesian	\N	2000-01-01	Male	123 main	[{"institution_name":"POLITEKNIK","years":"2012-2015","activity":"abc"}]	[{"company_name":"aaaamdb","position":"operator","years":"2012 -2015","job_desk":"dbshsb"}]	["skillll"]	eA-77u2jSfeivB78-mpIVu:APA91bGAofOmQ0Eq10VhIwk2L25wkA75aHGJyLSsmopU9Hp9BkknfUgTWfExy_9vFoqDZt8dOTB27Jc9ikXipHCPkv7kkfuX45LfgZoqJ0ePYHYZKJIYAdQ	\N	f	arc_other	Available	["mandarin - Basic"]	t	6	http://130.94.34.24/storage/selfies/selfie_20_1781607482.jpg	2026-06-18 02:06:27	verified	2026-06-18 02:07:02	ready	2026-06-18 02:07:02	f	f	2026-06-16	35000.00	4	\N	\N	\N	100	0	\N	\N	{"job_alerts":true,"chat_messages":true,"system_updates":true,"promotions":true}	f	\N	\N
\.


--
-- Data for Name: verification_codes; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.verification_codes (id, user_id, type, target, code, expires_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: verification_logs; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.verification_logs (id, entity_type, entity_id, action, notes, verified_by, verified_at) FROM stdin;
1	document	1	approved	\N	1	2026-06-15 04:01:37
2	document	2	approved	\N	1	2026-06-15 04:01:39
3	document	3	approved	\N	1	2026-06-15 04:43:12
4	document	4	approved	\N	1	2026-06-15 04:43:14
5	document	5	approved	\N	1	2026-06-15 04:43:15
6	document	6	approved	\N	1	2026-06-15 04:54:37
7	document	7	approved	\N	1	2026-06-15 04:54:38
8	employer	16	approved	All documents approved — badge granted	1	2026-06-15 08:32:17
9	employer	17	approved	All documents approved — badge granted	1	2026-06-15 13:53:44
10	document	10	approved	\N	1	2026-06-15 14:18:48
11	document	11	approved	\N	1	2026-06-15 14:18:54
12	worker	19	manual_override	Manual override by admin	1	2026-06-15 14:19:12
13	worker	21	approved	Selfie approved	1	2026-06-16 12:30:20
14	document	12	approved	\N	1	2026-06-16 12:30:30
15	document	13	approved	\N	1	2026-06-16 12:30:36
16	document	14	approved	\N	1	2026-06-16 12:30:41
17	document	24	approved	\N	1	2026-06-16 14:10:40
18	document	25	approved	\N	1	2026-06-16 14:10:46
19	document	27	approved	\N	1	2026-06-16 14:10:51
20	document	26	approved	\N	1	2026-06-16 14:10:57
21	document	26	approved	\N	1	2026-06-16 14:11:03
22	worker	22	approved	Selfie approved	1	2026-06-16 14:11:09
23	worker	23	approved	Selfie approved	1	2026-06-16 14:15:12
24	document	29	approved	\N	1	2026-06-16 14:15:15
25	document	29	approved	\N	1	2026-06-16 14:15:18
26	document	30	approved	\N	1	2026-06-16 14:15:21
27	document	31	approved	\N	1	2026-06-16 14:15:26
28	worker	24	approved	Selfie approved	1	2026-06-16 14:32:33
29	document	33	approved	\N	1	2026-06-16 14:32:40
30	worker	25	approved	Selfie approved	1	2026-06-16 14:35:36
31	document	35	approved	\N	1	2026-06-16 14:35:41
32	employer	26	approved	All documents approved — badge granted	1	2026-06-16 14:39:12
33	employer	30	approved	All documents approved — badge granted	1	2026-06-18 02:04:11
34	worker	20	approved	Selfie approved	1	2026-06-18 02:06:27
35	document	43	approved	\N	1	2026-06-18 02:07:02
36	worker	31	approved	Selfie approved	1	2026-06-19 03:07:15
37	document	44	approved	\N	1	2026-06-19 03:07:34
38	document	45	approved	\N	1	2026-06-19 03:12:51
39	document	46	approved	\N	1	2026-06-19 03:12:53
40	worker	32	approved	Selfie approved	1	2026-06-19 03:34:31
41	document	48	approved	\N	1	2026-06-19 03:34:39
42	employer	33	approved	All documents approved — badge granted	1	2026-06-19 05:54:59
\.


--
-- Data for Name: violation_histories; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.violation_histories (id, user_id, report_id, violation_type, description, points_deducted, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: worker_document_requirements; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.worker_document_requirements (id, user_id, document_type_id, upload_status, worker_document_id, required_by_date, created_at, updated_at) FROM stdin;
1	10	1	not_uploaded	\N	\N	2026-06-15 04:00:56	2026-06-15 04:00:56
2	10	2	not_uploaded	\N	\N	2026-06-15 04:00:56	2026-06-15 04:00:56
3	10	3	verified	1	\N	2026-06-15 04:00:56	2026-06-15 04:01:37
4	10	4	verified	2	\N	2026-06-15 04:01:07	2026-06-15 04:01:39
5	11	1	not_uploaded	\N	\N	2026-06-15 04:41:42	2026-06-15 04:41:42
6	11	2	not_uploaded	\N	\N	2026-06-15 04:41:42	2026-06-15 04:41:42
31	20	8	verified	42	\N	2026-06-16 10:58:01	2026-06-18 02:06:03
30	20	2	verified	16	\N	2026-06-16 10:58:01	2026-06-18 02:06:27
7	11	5	verified	3	\N	2026-06-15 04:41:42	2026-06-15 04:43:12
8	11	6	verified	4	\N	2026-06-15 04:41:42	2026-06-15 04:43:14
9	11	7	verified	5	\N	2026-06-15 04:41:42	2026-06-15 04:43:15
10	12	1	not_uploaded	\N	\N	2026-06-15 04:52:52	2026-06-15 04:52:52
11	12	2	not_uploaded	\N	\N	2026-06-15 04:52:52	2026-06-15 04:52:52
12	12	8	not_uploaded	\N	\N	2026-06-15 04:52:52	2026-06-15 04:52:52
49	23	2	verified	32	\N	2026-06-16 14:14:31	2026-06-16 14:15:12
14	12	10	verified	6	\N	2026-06-15 04:52:59	2026-06-15 04:54:37
13	12	9	verified	7	\N	2026-06-15 04:52:52	2026-06-15 04:54:38
15	13	1	not_uploaded	\N	\N	2026-06-15 08:09:40	2026-06-15 08:09:40
16	13	2	not_uploaded	\N	\N	2026-06-15 08:09:40	2026-06-15 08:09:40
17	13	15	uploaded	8	\N	2026-06-15 08:09:49	2026-06-15 08:09:49
18	14	1	not_uploaded	\N	\N	2026-06-15 08:11:49	2026-06-15 08:11:49
19	14	2	not_uploaded	\N	\N	2026-06-15 08:11:49	2026-06-15 08:11:49
20	14	16	uploaded	9	\N	2026-06-15 08:11:54	2026-06-15 08:11:54
21	18	1	not_uploaded	\N	\N	2026-06-15 13:58:32	2026-06-15 13:58:32
22	18	2	not_uploaded	\N	\N	2026-06-15 13:58:32	2026-06-15 13:58:32
23	18	3	not_uploaded	\N	\N	2026-06-15 13:58:32	2026-06-15 13:58:32
24	19	1	not_uploaded	\N	\N	2026-06-15 14:17:05	2026-06-15 14:17:05
25	19	2	not_uploaded	\N	\N	2026-06-15 14:17:05	2026-06-15 14:17:05
73	31	4	verified	46	\N	2026-06-19 03:06:28	2026-06-19 03:12:53
28	19	10	verified	10	\N	2026-06-15 14:18:03	2026-06-15 14:18:48
27	19	9	verified	11	\N	2026-06-15 14:17:05	2026-06-15 14:18:54
48	23	1	verified	29	\N	2026-06-16 14:14:31	2026-06-16 14:15:18
52	23	10	verified	30	\N	2026-06-16 14:14:58	2026-06-16 14:15:21
32	21	1	verified	12	\N	2026-06-16 12:28:00	2026-06-16 12:30:30
35	21	3	verified	13	\N	2026-06-16 12:28:00	2026-06-16 12:30:36
36	21	4	verified	14	\N	2026-06-16 12:29:12	2026-06-16 12:30:41
37	9	8	verified	15	\N	2026-06-16 12:43:09	2026-06-16 12:43:09
38	7	8	verified	17	\N	2026-06-16 12:43:09	2026-06-16 12:43:09
26	19	8	verified	18	\N	2026-06-15 14:17:05	2026-06-16 12:43:09
39	8	8	verified	19	\N	2026-06-16 12:43:09	2026-06-16 12:43:09
40	18	8	verified	20	\N	2026-06-16 12:43:09	2026-06-16 12:43:09
33	21	2	verified	21	\N	2026-06-16 12:28:00	2026-06-16 12:43:09
41	2	8	verified	22	\N	2026-06-16 12:43:09	2026-06-16 12:43:09
34	21	8	verified	23	\N	2026-06-16 12:28:00	2026-06-16 12:44:54
44	22	8	not_uploaded	\N	\N	2026-06-16 13:34:53	2026-06-16 13:34:53
51	23	9	verified	31	\N	2026-06-16 14:14:31	2026-06-16 14:15:26
55	24	8	not_uploaded	\N	\N	2026-06-16 14:28:04	2026-06-16 14:28:04
42	22	1	verified	24	\N	2026-06-16 13:34:53	2026-06-16 14:10:40
45	22	5	verified	25	\N	2026-06-16 13:34:53	2026-06-16 14:10:46
47	22	7	verified	27	\N	2026-06-16 13:34:53	2026-06-16 14:10:51
29	20	1	verified	43	\N	2026-06-16 10:58:01	2026-06-18 02:07:02
46	22	6	verified	26	\N	2026-06-16 13:34:53	2026-06-16 14:11:03
43	22	2	verified	28	\N	2026-06-16 13:34:53	2026-06-16 14:11:09
50	23	8	not_uploaded	\N	\N	2026-06-16 14:14:31	2026-06-16 14:14:31
54	24	2	verified	34	\N	2026-06-16 14:28:04	2026-06-16 14:32:33
53	24	1	verified	33	\N	2026-06-16 14:28:04	2026-06-16 14:32:40
58	25	8	not_uploaded	\N	\N	2026-06-16 14:34:28	2026-06-16 14:34:28
57	25	2	verified	36	\N	2026-06-16 14:34:28	2026-06-16 14:35:36
56	25	1	verified	35	\N	2026-06-16 14:34:28	2026-06-16 14:35:41
61	29	8	not_uploaded	\N	\N	2026-06-17 02:39:06	2026-06-17 02:39:06
59	29	1	uploaded	37	\N	2026-06-17 02:39:06	2026-06-17 02:39:20
60	29	2	uploaded	38	\N	2026-06-17 02:39:06	2026-06-17 02:39:20
62	29	5	uploaded	39	\N	2026-06-17 02:39:06	2026-06-17 02:40:10
63	29	6	uploaded	40	\N	2026-06-17 02:39:06	2026-06-17 02:40:10
64	29	7	uploaded	41	\N	2026-06-17 02:39:06	2026-06-17 02:40:10
70	32	5	uploaded	49	\N	2026-06-19 02:57:29	2026-06-19 03:33:53
71	32	6	uploaded	50	\N	2026-06-19 02:57:29	2026-06-19 03:33:54
66	31	2	verified	47	\N	2026-06-19 01:49:41	2026-06-19 03:07:15
65	31	1	verified	44	\N	2026-06-19 01:49:41	2026-06-19 03:07:34
67	31	3	verified	45	\N	2026-06-19 01:49:41	2026-06-19 03:12:51
72	32	7	uploaded	51	\N	2026-06-19 02:57:29	2026-06-19 03:33:54
69	32	2	verified	52	\N	2026-06-19 02:57:29	2026-06-19 03:34:31
68	32	1	verified	48	\N	2026-06-19 02:57:29	2026-06-19 03:34:39
74	32	8	verified	53	\N	2026-06-19 04:15:05	2026-06-19 04:15:05
76	35	2	not_uploaded	\N	\N	2026-06-24 06:54:52	2026-06-24 06:54:52
77	35	3	not_uploaded	\N	\N	2026-06-24 06:54:52	2026-06-24 06:54:52
75	35	1	uploaded	54	\N	2026-06-24 06:54:52	2026-06-24 06:55:50
\.


--
-- Data for Name: worker_documents; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.worker_documents (id, user_id, document_type_id, file_url, original_filename, review_status, review_note, reviewed_by, reviewed_at, created_at, updated_at, expiry_date) FROM stdin;
1	10	3	http://130.94.34.24/storage/worker_documents/doc_10_student_work_permit_1781496066.jpg	IMG-20181212-WA0019.jpg	approved	\N	1	2026-06-15 04:01:37	2026-06-15 04:01:06	2026-06-15 04:01:37	\N
2	10	4	http://130.94.34.24/storage/worker_documents/doc_10_enrollment_proof_1781496067.jpg	IMG-20181212-WA0024.jpg	approved	\N	1	2026-06-15 04:01:39	2026-06-15 04:01:07	2026-06-15 04:01:39	\N
3	11	5	http://130.94.34.24/storage/worker_documents/doc_11_transfer_document_1781498531.jpg	Screenshot_20260615_114153.jpg	approved	\N	1	2026-06-15 04:43:12	2026-06-15 04:42:11	2026-06-15 04:43:12	\N
4	11	6	http://130.94.34.24/storage/worker_documents/doc_11_work_contract_1781498532.jpg	Screenshot_20260615_114153.jpg	approved	\N	1	2026-06-15 04:43:14	2026-06-15 04:42:12	2026-06-15 04:43:14	\N
5	11	7	http://130.94.34.24/storage/worker_documents/doc_11_contract_ending_proof_1781498532.jpg	Screenshot_20260615_114153.jpg	approved	\N	1	2026-06-15 04:43:15	2026-06-15 04:42:12	2026-06-15 04:43:15	\N
6	12	10	http://130.94.34.24/storage/worker_documents/doc_12_work_permit_1781499179.jpg	Screenshot_20260615_114153.jpg	approved	\N	1	2026-06-15 04:54:37	2026-06-15 04:52:59	2026-06-15 04:54:37	\N
7	12	9	http://130.94.34.24/storage/worker_documents/doc_12_diploma_1781499180.jpg	Screenshot_20260615_114153.jpg	approved	\N	1	2026-06-15 04:54:38	2026-06-15 04:53:00	2026-06-15 04:54:38	\N
8	13	15	http://130.94.34.24/storage/worker_documents/doc_13_national_id_1781510989.jpg	Screenshot_20260615_114153.jpg	pending	\N	\N	\N	2026-06-15 08:09:49	2026-06-15 08:09:49	\N
9	14	16	http://130.94.34.24/storage/worker_documents/doc_14_identity_proof_1781511114.jpg	Screenshot_20260615_114153.jpg	pending	\N	\N	\N	2026-06-15 08:11:54	2026-06-15 08:11:54	\N
10	19	10	http://130.94.34.24/storage/worker_documents/doc_19_work_permit_1781533083.pdf	flow.pdf	approved	\N	1	2026-06-15 14:18:48	2026-06-15 14:18:03	2026-06-15 14:18:48	\N
11	19	9	http://130.94.34.24/storage/worker_documents/doc_19_diploma_1781533083.pdf	flow.pdf	approved	\N	1	2026-06-15 14:18:54	2026-06-15 14:18:03	2026-06-15 14:18:54	\N
12	21	1	http://130.94.34.24/storage/worker_personal_documents/personal_21_personal_id_1781612941.jpg	IMG_20260616_124811_310.jpg	approved	\N	1	2026-06-16 12:30:30	2026-06-16 12:29:01	2026-06-16 12:30:30	\N
13	21	3	http://130.94.34.24/storage/worker_documents/doc_21_student_work_permit_1781612952.jpg	Screenshot_20260616_184321.jpg	approved	\N	1	2026-06-16 12:30:36	2026-06-16 12:29:12	2026-06-16 12:30:36	\N
14	21	4	http://130.94.34.24/storage/worker_documents/doc_21_enrollment_proof_1781612952.jpg	Screenshot_20260616_184324.jpg	approved	\N	1	2026-06-16 12:30:41	2026-06-16 12:29:12	2026-06-16 12:30:41	\N
15	9	8	https://example.com/cvs/somchai_prasert.pdf	cv.pdf	approved	\N	\N	\N	2026-06-16 12:43:09	2026-06-16 12:43:09	\N
17	7	8	https://example.com/cvs/nguyen_lan.pdf	cv.pdf	approved	\N	\N	\N	2026-06-16 12:43:09	2026-06-16 12:43:09	\N
18	19	8	http://130.94.34.24/storage/cvs/cv_19_1781571259.pdf	cv.pdf	approved	\N	\N	\N	2026-06-16 12:43:09	2026-06-16 12:43:09	\N
19	8	8	https://example.com/cvs/siti_rahayu.pdf	cv.pdf	approved	\N	\N	\N	2026-06-16 12:43:09	2026-06-16 12:43:09	\N
20	18	8	http://130.94.34.24/storage/cvs/cv_18_1781532846.pdf	cv.pdf	approved	\N	\N	\N	2026-06-16 12:43:09	2026-06-16 12:43:09	\N
21	21	2	http://130.94.34.24/storage/selfies/selfie_21_1781612881.jpg	selfie.jpg	approved	\N	\N	\N	2026-06-16 12:43:09	2026-06-16 12:43:09	\N
22	2	8	https://example.com/cvs/maria_santos.pdf	cv.pdf	approved	\N	\N	\N	2026-06-16 12:43:09	2026-06-16 12:43:09	\N
23	21	8	http://130.94.34.24/storage/cvs/cv_21_1781613894.pdf	job_board_profile_logic_simple.pdf	approved	\N	\N	\N	2026-06-16 12:44:54	2026-06-16 12:44:54	\N
24	22	1	http://130.94.34.24/storage/worker_personal_documents/personal_22_personal_id_1781616919.jpg	20260616_155737.jpg	approved	\N	1	2026-06-16 14:10:40	2026-06-16 13:35:20	2026-06-16 14:10:40	\N
25	22	5	http://130.94.34.24/storage/worker_documents/doc_22_transfer_document_1781619003.jpg	Screenshot_20260616_210603_myXL.jpg	approved	\N	1	2026-06-16 14:10:46	2026-06-16 14:10:03	2026-06-16 14:10:46	\N
27	22	7	http://130.94.34.24/storage/worker_documents/doc_22_contract_ending_proof_1781619004.jpg	Screenshot_20260616_210603_myXL.jpg	approved	\N	1	2026-06-16 14:10:51	2026-06-16 14:10:04	2026-06-16 14:10:51	\N
26	22	6	http://130.94.34.24/storage/worker_documents/doc_22_work_contract_1781619003.jpg	Screenshot_20260616_210603_myXL.jpg	approved	\N	1	2026-06-16 14:11:03	2026-06-16 14:10:03	2026-06-16 14:11:03	\N
28	22	2	http://130.94.34.24/storage/selfies/selfie_22_1781616894.jpg	selfie.jpg	approved	\N	1	2026-06-16 14:11:09	2026-06-16 14:11:09	2026-06-16 14:11:09	\N
37	29	1	http://130.94.34.24/storage/worker_personal_documents/personal_29_personal_id_1781663960.pdf	job_board_profile_logic_simple.pdf	pending	\N	\N	\N	2026-06-17 02:39:20	2026-06-17 02:39:20	\N
32	23	2	http://130.94.34.24/storage/selfies/selfie_23_1781619272.jpg	selfie.jpg	approved	\N	1	2026-06-16 14:15:12	2026-06-16 14:15:12	2026-06-16 14:15:12	\N
29	23	1	http://130.94.34.24/storage/worker_personal_documents/personal_23_personal_id_1781619291.jpg	Screenshot_20260616_210603_myXL.jpg	approved	\N	1	2026-06-16 14:15:18	2026-06-16 14:14:39	2026-06-16 14:15:18	\N
30	23	10	http://130.94.34.24/storage/worker_documents/doc_23_work_permit_1781619298.jpg	Screenshot_20260616_210603_myXL.jpg	approved	\N	1	2026-06-16 14:15:21	2026-06-16 14:14:58	2026-06-16 14:15:21	\N
31	23	9	http://130.94.34.24/storage/worker_documents/doc_23_diploma_1781619299.jpg	Screenshot_20260616_210603_myXL.jpg	approved	\N	1	2026-06-16 14:15:26	2026-06-16 14:14:59	2026-06-16 14:15:26	\N
34	24	2	http://130.94.34.24/storage/selfies/selfie_24_1781620084.jpg	selfie.jpg	approved	\N	1	2026-06-16 14:32:33	2026-06-16 14:32:33	2026-06-16 14:32:33	\N
33	24	1	http://130.94.34.24/storage/worker_personal_documents/personal_24_personal_id_1781620331.jpg	Screenshot_20260616_212820.jpg	approved	\N	1	2026-06-16 14:32:40	2026-06-16 14:28:12	2026-06-16 14:32:40	\N
36	25	2	http://130.94.34.24/storage/selfies/selfie_25_1781620468.jpg	selfie.jpg	approved	\N	1	2026-06-16 14:35:36	2026-06-16 14:35:36	2026-06-16 14:35:36	\N
35	25	1	http://130.94.34.24/storage/worker_personal_documents/personal_25_personal_id_1781620516.jpg	Screenshot_20260616_212820.jpg	approved	\N	1	2026-06-16 14:35:41	2026-06-16 14:34:33	2026-06-16 14:35:41	\N
38	29	2	http://130.94.34.24/storage/worker_personal_documents/personal_29_selfie_1781663960.jpg	scaled_0009a9a5-e88f-4c88-b1a9-7b1ba682f6d67843913009647170526.jpg	pending	\N	\N	\N	2026-06-17 02:39:20	2026-06-17 02:39:20	\N
39	29	5	http://130.94.34.24/storage/worker_documents/doc_29_transfer_document_1781664010.pdf	job_board_profile_logic_simple.pdf	pending	\N	\N	\N	2026-06-17 02:40:10	2026-06-17 02:40:10	\N
40	29	6	http://130.94.34.24/storage/worker_documents/doc_29_work_contract_1781664010.pdf	job_board_profile_logic_simple.pdf	pending	\N	\N	\N	2026-06-17 02:40:10	2026-06-17 02:40:10	\N
41	29	7	http://130.94.34.24/storage/worker_documents/doc_29_contract_ending_proof_1781664010.pdf	job_board_profile_logic_simple.pdf	pending	\N	\N	\N	2026-06-17 02:40:10	2026-06-17 02:40:10	\N
42	20	8	http://130.94.34.24/storage/cvs/cv_20_1781748363.pdf	job_board_profile_logic_simple.pdf	approved	\N	\N	\N	2026-06-18 02:06:03	2026-06-18 02:06:03	\N
16	20	2	http://130.94.34.24/storage/selfies/selfie_20_1781607482.jpg	selfie.jpg	approved	\N	1	2026-06-18 02:06:27	2026-06-16 12:43:09	2026-06-18 02:06:27	\N
43	20	1	http://130.94.34.24/storage/worker_personal_documents/personal_20_personal_id_1781748406.jpg	Screenshot_20260617_230142_Facebook.jpg	approved	\N	1	2026-06-18 02:07:02	2026-06-18 02:06:46	2026-06-18 02:07:02	\N
47	31	2	http://130.94.34.24/storage/selfies/selfie_31_1781833781.jpg	selfie.jpg	approved	\N	1	2026-06-19 03:07:15	2026-06-19 03:07:15	2026-06-19 03:07:15	\N
44	31	1	http://130.94.34.24/storage/worker_personal_documents/personal_31_personal_id_1781837971.pdf	file-sample_150kB.pdf	approved	\N	1	2026-06-19 03:07:34	2026-06-19 01:56:28	2026-06-19 03:07:34	\N
45	31	3	http://130.94.34.24/storage/worker_documents/doc_31_student_work_permit_1781838388.pdf	file-sample_150kB.pdf	approved	\N	1	2026-06-19 03:12:51	2026-06-19 03:06:28	2026-06-19 03:12:51	\N
46	31	4	http://130.94.34.24/storage/worker_documents/doc_31_enrollment_proof_1781838388.pdf	file-sample_150kB.pdf	approved	\N	1	2026-06-19 03:12:53	2026-06-19 03:06:28	2026-06-19 03:12:53	\N
49	32	5	http://130.94.34.24/storage/worker_documents/doc_32_transfer_document_1781840033.pdf	file-sample_150kB.pdf	pending	\N	\N	\N	2026-06-19 03:33:53	2026-06-19 03:33:53	\N
50	32	6	http://130.94.34.24/storage/worker_documents/doc_32_work_contract_1781840034.pdf	file-sample_150kB.pdf	pending	\N	\N	\N	2026-06-19 03:33:54	2026-06-19 03:33:54	\N
51	32	7	http://130.94.34.24/storage/worker_documents/doc_32_contract_ending_proof_1781840034.pdf	file-sample_150kB.pdf	pending	\N	\N	\N	2026-06-19 03:33:54	2026-06-19 03:33:54	\N
52	32	2	http://130.94.34.24/storage/selfies/selfie_32_1781837850.jpg	selfie.jpg	approved	\N	1	2026-06-19 03:34:31	2026-06-19 03:34:31	2026-06-19 03:34:31	\N
48	32	1	http://130.94.34.24/storage/worker_personal_documents/personal_32_personal_id_1781840020.pdf	file-sample_150kB.pdf	approved	\N	1	2026-06-19 03:34:39	2026-06-19 03:33:40	2026-06-19 03:34:39	\N
53	32	8	http://130.94.34.24/storage/cvs/cv_32_1781842505.pdf	file-sample_150kB.pdf	approved	\N	\N	\N	2026-06-19 04:15:05	2026-06-19 04:15:05	\N
54	35	1	http://130.94.34.24/storage/worker_personal_documents/personal_35_personal_id_1782284150.pdf	file-sample_150kB.pdf	pending	\N	\N	\N	2026-06-24 06:55:50	2026-06-24 06:55:50	\N
\.


--
-- Data for Name: worker_job_types; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.worker_job_types (id, user_id, job_type_id, years_of_experience, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: worker_languages; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.worker_languages (id, user_id, language_id, proficiency_level, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: worker_types; Type: TABLE DATA; Schema: public; Owner: migrant_user
--

COPY public.worker_types (id, worker_type_name, slug, description, requires_arc, auto_ready_to_work, eligible_to_work, created_at, updated_at) FROM stdin;
1	Student ARC	student	International student with valid ARC — permitted to work part-time (up to 20 hrs/week)	t	f	t	2026-06-15 03:56:36	2026-06-15 03:56:36
2	Blue Collar ARC	blue_collar	Migrant worker with employer-sponsored ARC (factory, care, construction)	t	f	t	2026-06-15 03:56:36	2026-06-15 03:56:36
3	White Collar ARC	white_collar	Professional/white collar worker — requires employer sponsorship	t	f	t	2026-06-15 03:56:36	2026-06-15 03:56:36
4	ARC Other	arc_other	Worker with open work rights ARC (spouse of citizen, JFRV, etc.)	t	t	t	2026-06-15 03:56:36	2026-06-15 03:56:36
6	Taiwanese	taiwanese	ROC National — no ARC or work permit required	f	t	t	2026-06-15 03:56:36	2026-06-15 03:56:36
7	Not Sure	not_sure	Status unknown — cannot search for work until resolved	f	f	f	2026-06-15 03:56:36	2026-06-15 03:56:36
8	Other	other	Other visa status — admin review required	f	f	t	2026-06-15 03:56:36	2026-06-15 03:56:36
5	APRC / Gold Card	aprc	Permanent resident or Gold Card holder — full open work rights	f	t	t	2026-06-15 03:56:36	2026-06-16 07:18:56
11	Employment Gold Card	gold_card	Employment Gold Card holder — full open work rights	f	t	t	2026-06-19 06:36:15	2026-06-19 06:36:15
12	Spouse of ROC Citizen	spouse_roc	Spouse of ROC Citizen (JFRV ARC) — full open work rights	t	t	t	2026-06-19 06:36:15	2026-06-19 06:36:15
\.


--
-- Name: ad_packages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.ad_packages_id_seq', 1, false);


--
-- Name: advertisements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.advertisements_id_seq', 1, false);


--
-- Name: application_status_history_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.application_status_history_id_seq', 5, true);


--
-- Name: application_status_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.application_status_logs_id_seq', 9, true);


--
-- Name: audit_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.audit_logs_id_seq', 1, false);


--
-- Name: blocked_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.blocked_users_id_seq', 1, false);


--
-- Name: categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.categories_id_seq', 8, true);


--
-- Name: chat_conversations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.chat_conversations_id_seq', 2, true);


--
-- Name: chat_messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.chat_messages_id_seq', 145, true);


--
-- Name: cities_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.cities_id_seq', 31, true);


--
-- Name: document_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.document_types_id_seq', 17, true);


--
-- Name: employer_documents_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.employer_documents_id_seq', 17, true);


--
-- Name: employer_staff_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.employer_staff_id_seq', 1, false);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: industries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.industries_id_seq', 9, true);


--
-- Name: job_applications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.job_applications_id_seq', 11, true);


--
-- Name: job_listings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.job_listings_id_seq', 18, true);


--
-- Name: job_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.job_types_id_seq', 27, true);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: languages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.languages_id_seq', 13, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.migrations_id_seq', 68, true);


--
-- Name: nationalities_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.nationalities_id_seq', 8, true);


--
-- Name: notifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.notifications_id_seq', 10, true);


--
-- Name: payments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.payments_id_seq', 1, false);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 96, true);


--
-- Name: reports_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.reports_id_seq', 1, false);


--
-- Name: safety_checks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.safety_checks_id_seq', 6, true);


--
-- Name: skills_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.skills_id_seq', 1, false);


--
-- Name: subscriptions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.subscriptions_id_seq', 1, false);


--
-- Name: translation_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.translation_logs_id_seq', 1, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.users_id_seq', 35, true);


--
-- Name: verification_codes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.verification_codes_id_seq', 1, true);


--
-- Name: verification_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.verification_logs_id_seq', 42, true);


--
-- Name: violation_histories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.violation_histories_id_seq', 1, false);


--
-- Name: worker_document_requirements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.worker_document_requirements_id_seq', 77, true);


--
-- Name: worker_documents_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.worker_documents_id_seq', 54, true);


--
-- Name: worker_job_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.worker_job_types_id_seq', 1, false);


--
-- Name: worker_languages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.worker_languages_id_seq', 1, false);


--
-- Name: worker_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: migrant_user
--

SELECT pg_catalog.setval('public.worker_types_id_seq', 12, true);


--
-- Name: ad_packages ad_packages_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.ad_packages
    ADD CONSTRAINT ad_packages_pkey PRIMARY KEY (id);


--
-- Name: advertisements advertisements_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.advertisements
    ADD CONSTRAINT advertisements_pkey PRIMARY KEY (id);


--
-- Name: application_status_history application_status_history_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.application_status_history
    ADD CONSTRAINT application_status_history_pkey PRIMARY KEY (id);


--
-- Name: application_status_logs application_status_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.application_status_logs
    ADD CONSTRAINT application_status_logs_pkey PRIMARY KEY (id);


--
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- Name: blocked_users blocked_users_blocker_id_blocked_id_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.blocked_users
    ADD CONSTRAINT blocked_users_blocker_id_blocked_id_unique UNIQUE (blocker_id, blocked_id);


--
-- Name: blocked_users blocked_users_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.blocked_users
    ADD CONSTRAINT blocked_users_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: categories categories_name_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_name_unique UNIQUE (name);


--
-- Name: categories categories_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_pkey PRIMARY KEY (id);


--
-- Name: categories categories_slug_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_slug_unique UNIQUE (slug);


--
-- Name: chat_conversations chat_conversations_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.chat_conversations
    ADD CONSTRAINT chat_conversations_pkey PRIMARY KEY (id);


--
-- Name: chat_conversations chat_conversations_user_a_id_user_b_id_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.chat_conversations
    ADD CONSTRAINT chat_conversations_user_a_id_user_b_id_unique UNIQUE (user_a_id, user_b_id);


--
-- Name: chat_messages chat_messages_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_pkey PRIMARY KEY (id);


--
-- Name: cities cities_name_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.cities
    ADD CONSTRAINT cities_name_unique UNIQUE (name);


--
-- Name: cities cities_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.cities
    ADD CONSTRAINT cities_pkey PRIMARY KEY (id);


--
-- Name: document_types document_types_document_type_name_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.document_types
    ADD CONSTRAINT document_types_document_type_name_unique UNIQUE (document_type_name);


--
-- Name: document_types document_types_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.document_types
    ADD CONSTRAINT document_types_pkey PRIMARY KEY (id);


--
-- Name: document_types document_types_slug_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.document_types
    ADD CONSTRAINT document_types_slug_unique UNIQUE (slug);


--
-- Name: employer_documents employer_documents_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.employer_documents
    ADD CONSTRAINT employer_documents_pkey PRIMARY KEY (id);


--
-- Name: employer_staff employer_staff_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.employer_staff
    ADD CONSTRAINT employer_staff_pkey PRIMARY KEY (id);


--
-- Name: employer_staff employer_staff_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.employer_staff
    ADD CONSTRAINT employer_staff_user_id_unique UNIQUE (user_id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: industries industries_name_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.industries
    ADD CONSTRAINT industries_name_unique UNIQUE (name);


--
-- Name: industries industries_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.industries
    ADD CONSTRAINT industries_pkey PRIMARY KEY (id);


--
-- Name: industries industries_slug_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.industries
    ADD CONSTRAINT industries_slug_unique UNIQUE (slug);


--
-- Name: job_applications job_applications_job_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.job_applications
    ADD CONSTRAINT job_applications_job_id_user_id_unique UNIQUE (job_id, user_id);


--
-- Name: job_applications job_applications_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.job_applications
    ADD CONSTRAINT job_applications_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: job_listings job_listings_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.job_listings
    ADD CONSTRAINT job_listings_pkey PRIMARY KEY (id);


--
-- Name: job_types job_types_job_type_name_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.job_types
    ADD CONSTRAINT job_types_job_type_name_unique UNIQUE (job_type_name);


--
-- Name: job_types job_types_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.job_types
    ADD CONSTRAINT job_types_pkey PRIMARY KEY (id);


--
-- Name: job_types job_types_slug_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.job_types
    ADD CONSTRAINT job_types_slug_unique UNIQUE (slug);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: languages languages_language_code_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.languages
    ADD CONSTRAINT languages_language_code_unique UNIQUE (language_code);


--
-- Name: languages languages_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.languages
    ADD CONSTRAINT languages_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: nationalities nationalities_code_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.nationalities
    ADD CONSTRAINT nationalities_code_unique UNIQUE (code);


--
-- Name: nationalities nationalities_name_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.nationalities
    ADD CONSTRAINT nationalities_name_unique UNIQUE (name);


--
-- Name: nationalities nationalities_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.nationalities
    ADD CONSTRAINT nationalities_pkey PRIMARY KEY (id);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: payments payments_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: reports reports_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.reports
    ADD CONSTRAINT reports_pkey PRIMARY KEY (id);


--
-- Name: safety_checks safety_checks_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.safety_checks
    ADD CONSTRAINT safety_checks_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: skills skills_name_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.skills
    ADD CONSTRAINT skills_name_unique UNIQUE (name);


--
-- Name: skills skills_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.skills
    ADD CONSTRAINT skills_pkey PRIMARY KEY (id);


--
-- Name: skills skills_slug_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.skills
    ADD CONSTRAINT skills_slug_unique UNIQUE (slug);


--
-- Name: subscriptions subscriptions_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_pkey PRIMARY KEY (id);


--
-- Name: translation_logs translation_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.translation_logs
    ADD CONSTRAINT translation_logs_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: verification_codes verification_codes_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.verification_codes
    ADD CONSTRAINT verification_codes_pkey PRIMARY KEY (id);


--
-- Name: verification_logs verification_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.verification_logs
    ADD CONSTRAINT verification_logs_pkey PRIMARY KEY (id);


--
-- Name: violation_histories violation_histories_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.violation_histories
    ADD CONSTRAINT violation_histories_pkey PRIMARY KEY (id);


--
-- Name: worker_document_requirements worker_document_requirements_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_document_requirements
    ADD CONSTRAINT worker_document_requirements_pkey PRIMARY KEY (id);


--
-- Name: worker_document_requirements worker_document_requirements_user_id_document_type_id_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_document_requirements
    ADD CONSTRAINT worker_document_requirements_user_id_document_type_id_unique UNIQUE (user_id, document_type_id);


--
-- Name: worker_documents worker_documents_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_documents
    ADD CONSTRAINT worker_documents_pkey PRIMARY KEY (id);


--
-- Name: worker_job_types worker_job_types_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_job_types
    ADD CONSTRAINT worker_job_types_pkey PRIMARY KEY (id);


--
-- Name: worker_job_types worker_job_types_user_id_job_type_id_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_job_types
    ADD CONSTRAINT worker_job_types_user_id_job_type_id_unique UNIQUE (user_id, job_type_id);


--
-- Name: worker_languages worker_languages_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_languages
    ADD CONSTRAINT worker_languages_pkey PRIMARY KEY (id);


--
-- Name: worker_languages worker_languages_user_id_language_id_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_languages
    ADD CONSTRAINT worker_languages_user_id_language_id_unique UNIQUE (user_id, language_id);


--
-- Name: worker_types worker_types_pkey; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_types
    ADD CONSTRAINT worker_types_pkey PRIMARY KEY (id);


--
-- Name: worker_types worker_types_slug_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_types
    ADD CONSTRAINT worker_types_slug_unique UNIQUE (slug);


--
-- Name: worker_types worker_types_worker_type_name_unique; Type: CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_types
    ADD CONSTRAINT worker_types_worker_type_name_unique UNIQUE (worker_type_name);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: chat_messages_is_read_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX chat_messages_is_read_index ON public.chat_messages USING btree (is_read);


--
-- Name: chat_messages_sender_id_receiver_id_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX chat_messages_sender_id_receiver_id_index ON public.chat_messages USING btree (sender_id, receiver_id);


--
-- Name: employer_documents_user_id_status_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX employer_documents_user_id_status_index ON public.employer_documents USING btree (user_id, status);


--
-- Name: employer_staff_agency_employer_id_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX employer_staff_agency_employer_id_index ON public.employer_staff USING btree (agency_employer_id);


--
-- Name: job_applications_status_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX job_applications_status_index ON public.job_applications USING btree (status);


--
-- Name: job_listings_category_location_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX job_listings_category_location_index ON public.job_listings USING btree (category, location);


--
-- Name: job_listings_is_urgent_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX job_listings_is_urgent_index ON public.job_listings USING btree (is_urgent);


--
-- Name: job_listings_status_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX job_listings_status_index ON public.job_listings USING btree (status);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: notifications_created_at_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX notifications_created_at_index ON public.notifications USING btree (created_at);


--
-- Name: notifications_user_id_read_at_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX notifications_user_id_read_at_index ON public.notifications USING btree (user_id, read_at);


--
-- Name: payments_user_id_status_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX payments_user_id_status_index ON public.payments USING btree (user_id, status);


--
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: safety_checks_user_id_created_at_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX safety_checks_user_id_created_at_index ON public.safety_checks USING btree (user_id, created_at);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: subscriptions_user_id_status_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX subscriptions_user_id_status_index ON public.subscriptions USING btree (user_id, status);


--
-- Name: translation_logs_chat_message_id_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX translation_logs_chat_message_id_index ON public.translation_logs USING btree (chat_message_id);


--
-- Name: translation_logs_user_id_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX translation_logs_user_id_index ON public.translation_logs USING btree (user_id);


--
-- Name: users_role_badges_idx; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX users_role_badges_idx ON public.users USING btree (role, ready_to_work_status, verified_badge_status);


--
-- Name: users_role_city_idx; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX users_role_city_idx ON public.users USING btree (role, current_city);


--
-- Name: users_worker_type_id_idx; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX users_worker_type_id_idx ON public.users USING btree (worker_type_id);


--
-- Name: verification_codes_user_id_type_code_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX verification_codes_user_id_type_code_index ON public.verification_codes USING btree (user_id, type, code);


--
-- Name: verification_logs_entity_type_entity_id_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX verification_logs_entity_type_entity_id_index ON public.verification_logs USING btree (entity_type, entity_id);


--
-- Name: verification_logs_verified_by_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX verification_logs_verified_by_index ON public.verification_logs USING btree (verified_by);


--
-- Name: worker_documents_document_type_id_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX worker_documents_document_type_id_index ON public.worker_documents USING btree (document_type_id);


--
-- Name: worker_documents_user_id_review_status_index; Type: INDEX; Schema: public; Owner: migrant_user
--

CREATE INDEX worker_documents_user_id_review_status_index ON public.worker_documents USING btree (user_id, review_status);


--
-- Name: advertisements advertisements_ad_package_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.advertisements
    ADD CONSTRAINT advertisements_ad_package_id_foreign FOREIGN KEY (ad_package_id) REFERENCES public.ad_packages(id) ON DELETE CASCADE;


--
-- Name: advertisements advertisements_job_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.advertisements
    ADD CONSTRAINT advertisements_job_id_foreign FOREIGN KEY (job_id) REFERENCES public.job_listings(id) ON DELETE CASCADE;


--
-- Name: advertisements advertisements_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.advertisements
    ADD CONSTRAINT advertisements_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: application_status_history application_status_history_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.application_status_history
    ADD CONSTRAINT application_status_history_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.job_applications(id) ON DELETE CASCADE;


--
-- Name: application_status_history application_status_history_recorded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.application_status_history
    ADD CONSTRAINT application_status_history_recorded_by_foreign FOREIGN KEY (recorded_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: application_status_logs application_status_logs_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.application_status_logs
    ADD CONSTRAINT application_status_logs_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.job_applications(id) ON DELETE CASCADE;


--
-- Name: audit_logs audit_logs_admin_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_admin_id_foreign FOREIGN KEY (admin_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: blocked_users blocked_users_blocked_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.blocked_users
    ADD CONSTRAINT blocked_users_blocked_id_foreign FOREIGN KEY (blocked_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: blocked_users blocked_users_blocker_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.blocked_users
    ADD CONSTRAINT blocked_users_blocker_id_foreign FOREIGN KEY (blocker_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: chat_conversations chat_conversations_closed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.chat_conversations
    ADD CONSTRAINT chat_conversations_closed_by_foreign FOREIGN KEY (closed_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: chat_conversations chat_conversations_user_a_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.chat_conversations
    ADD CONSTRAINT chat_conversations_user_a_id_foreign FOREIGN KEY (user_a_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: chat_conversations chat_conversations_user_b_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.chat_conversations
    ADD CONSTRAINT chat_conversations_user_b_id_foreign FOREIGN KEY (user_b_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: chat_messages chat_messages_application_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_application_id_foreign FOREIGN KEY (application_id) REFERENCES public.job_applications(id) ON DELETE SET NULL;


--
-- Name: chat_messages chat_messages_job_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_job_id_foreign FOREIGN KEY (job_id) REFERENCES public.job_listings(id) ON DELETE SET NULL;


--
-- Name: chat_messages chat_messages_receiver_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_receiver_id_foreign FOREIGN KEY (receiver_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: chat_messages chat_messages_sender_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_sender_id_foreign FOREIGN KEY (sender_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: document_types document_types_worker_type_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.document_types
    ADD CONSTRAINT document_types_worker_type_id_foreign FOREIGN KEY (worker_type_id) REFERENCES public.worker_types(id) ON DELETE SET NULL;


--
-- Name: employer_documents employer_documents_reviewed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.employer_documents
    ADD CONSTRAINT employer_documents_reviewed_by_foreign FOREIGN KEY (reviewed_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: employer_documents employer_documents_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.employer_documents
    ADD CONSTRAINT employer_documents_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: employer_staff employer_staff_agency_employer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.employer_staff
    ADD CONSTRAINT employer_staff_agency_employer_id_foreign FOREIGN KEY (agency_employer_id) REFERENCES public.users(id);


--
-- Name: employer_staff employer_staff_approved_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.employer_staff
    ADD CONSTRAINT employer_staff_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: employer_staff employer_staff_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.employer_staff
    ADD CONSTRAINT employer_staff_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: job_applications job_applications_job_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.job_applications
    ADD CONSTRAINT job_applications_job_id_foreign FOREIGN KEY (job_id) REFERENCES public.job_listings(id) ON DELETE CASCADE;


--
-- Name: job_applications job_applications_status_snapshot_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.job_applications
    ADD CONSTRAINT job_applications_status_snapshot_id_foreign FOREIGN KEY (status_snapshot_id) REFERENCES public.application_status_history(id) ON DELETE SET NULL;


--
-- Name: job_applications job_applications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.job_applications
    ADD CONSTRAINT job_applications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: job_listings job_listings_employer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.job_listings
    ADD CONSTRAINT job_listings_employer_id_foreign FOREIGN KEY (employer_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: job_listings job_listings_job_type_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.job_listings
    ADD CONSTRAINT job_listings_job_type_id_foreign FOREIGN KEY (job_type_id) REFERENCES public.job_types(id) ON DELETE SET NULL;


--
-- Name: notifications notifications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: payments payments_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: reports reports_chat_message_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.reports
    ADD CONSTRAINT reports_chat_message_id_foreign FOREIGN KEY (chat_message_id) REFERENCES public.chat_messages(id) ON DELETE SET NULL;


--
-- Name: reports reports_job_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.reports
    ADD CONSTRAINT reports_job_id_foreign FOREIGN KEY (job_id) REFERENCES public.job_listings(id) ON DELETE CASCADE;


--
-- Name: reports reports_reported_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.reports
    ADD CONSTRAINT reports_reported_id_foreign FOREIGN KEY (reported_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: reports reports_reporter_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.reports
    ADD CONSTRAINT reports_reporter_id_foreign FOREIGN KEY (reporter_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: safety_checks safety_checks_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.safety_checks
    ADD CONSTRAINT safety_checks_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: subscriptions subscriptions_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: translation_logs translation_logs_chat_message_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.translation_logs
    ADD CONSTRAINT translation_logs_chat_message_id_foreign FOREIGN KEY (chat_message_id) REFERENCES public.chat_messages(id) ON DELETE CASCADE;


--
-- Name: translation_logs translation_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.translation_logs
    ADD CONSTRAINT translation_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: users users_worker_type_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_worker_type_id_foreign FOREIGN KEY (worker_type_id) REFERENCES public.worker_types(id) ON DELETE SET NULL;


--
-- Name: verification_codes verification_codes_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.verification_codes
    ADD CONSTRAINT verification_codes_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: verification_logs verification_logs_verified_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.verification_logs
    ADD CONSTRAINT verification_logs_verified_by_foreign FOREIGN KEY (verified_by) REFERENCES public.users(id);


--
-- Name: violation_histories violation_histories_report_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.violation_histories
    ADD CONSTRAINT violation_histories_report_id_foreign FOREIGN KEY (report_id) REFERENCES public.reports(id) ON DELETE SET NULL;


--
-- Name: violation_histories violation_histories_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.violation_histories
    ADD CONSTRAINT violation_histories_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: worker_document_requirements worker_document_requirements_document_type_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_document_requirements
    ADD CONSTRAINT worker_document_requirements_document_type_id_foreign FOREIGN KEY (document_type_id) REFERENCES public.document_types(id);


--
-- Name: worker_document_requirements worker_document_requirements_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_document_requirements
    ADD CONSTRAINT worker_document_requirements_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: worker_document_requirements worker_document_requirements_worker_document_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_document_requirements
    ADD CONSTRAINT worker_document_requirements_worker_document_id_foreign FOREIGN KEY (worker_document_id) REFERENCES public.worker_documents(id) ON DELETE SET NULL;


--
-- Name: worker_documents worker_documents_document_type_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_documents
    ADD CONSTRAINT worker_documents_document_type_id_foreign FOREIGN KEY (document_type_id) REFERENCES public.document_types(id);


--
-- Name: worker_documents worker_documents_reviewed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_documents
    ADD CONSTRAINT worker_documents_reviewed_by_foreign FOREIGN KEY (reviewed_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: worker_documents worker_documents_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_documents
    ADD CONSTRAINT worker_documents_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: worker_job_types worker_job_types_job_type_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_job_types
    ADD CONSTRAINT worker_job_types_job_type_id_foreign FOREIGN KEY (job_type_id) REFERENCES public.job_types(id);


--
-- Name: worker_job_types worker_job_types_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_job_types
    ADD CONSTRAINT worker_job_types_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: worker_languages worker_languages_language_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_languages
    ADD CONSTRAINT worker_languages_language_id_foreign FOREIGN KEY (language_id) REFERENCES public.languages(id);


--
-- Name: worker_languages worker_languages_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: migrant_user
--

ALTER TABLE ONLY public.worker_languages
    ADD CONSTRAINT worker_languages_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict iXUkdfHvMcjUPTCgROAMnCtbkzybeqF3sEGNOKHWdiY1el5nh3R5UpeBcumOx8h

